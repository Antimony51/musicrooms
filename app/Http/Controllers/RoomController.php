<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Room;
use App\User;
use App\Track;
use App\RoomState;
use Auth;
use DateInterval;
use DateTimeImmutable;

class RoomController extends Controller
{
    public function show(Room $room, Request $request)
    {
        return view('room.main', compact('room'));
    }

    public function showPublicRooms()
    {
        $rooms = Room::whereVisibility('public')->paginate(20);
        $title = "Public Rooms";
        $emptyMessage = "There are no public rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showSavedRooms(Request $request)
    {
        $rooms = $request->user()->savedRooms()->paginate(20);
        $title = "Saved Rooms";
        $emptyMessage = "You have no saved rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showAllRooms()
    {
        $rooms = Room::paginate(20);
        $title = "All Rooms";
        $emptyMessage = "There are no rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showMyRooms(Request $request)
    {
        $rooms = $request->user()->rooms()->paginate(20);
        $title = "My Rooms";
        $emptyMessage = "You don't own any rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function syncMe (Room $room, Request $request){
        $roomState = RoomState::get($room);
        $roomState->updateState();
        if (Auth::check()){
            $roomState->userSeen($request->user()->name);
            RoomState::put($room, $roomState);
        }
        return json_encode($roomState);
    }

    public function join (Room $room, Request $request){
        if (Auth::check()) {
            $roomState = RoomState::get($room);
            $roomState->userJoin($request->user()->name);
            RoomState::put($room, $roomState);
        }
        return json_encode(RoomState::get($room));
    }

    public function leave (Room $room, Request $request){
        if (Auth::check()) {
            $roomState = RoomState::get($room);
            $roomState->userLeave($request->user()->name);
            RoomState::put($room, $roomState);
        }
    }

    public function addTrack (Room $room, Request $request){
        if (Auth::check()) {
            $user = $request->user();
            $roomState = RoomState::get($room);
            if ($roomState->hasUser($user->name)){
                $type = $request->input('type');
                $uri = $request->input('uri');
                $newTrack = new Track();
                if ($type == 'youtube'){
                    $ch = curl_init(
                        'https://www.googleapis.com/youtube/v3/videos' .
                        '?part=status,snippet,contentDetails' .
                        '&id=' . $uri .
                        '&key=' . config('services.youtube.key')
                    );
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $data = curl_exec($ch);
                    if ($data === false){
                        abort(502);
                    }
                    curl_close($ch);
                    $data = json_decode($data);
                    if (sizeof($data->items) == 0){
                        abort(404, 'Video does not exist');
                    }
                    $status = $data->items[0]->status;
                    $snippet = $data->items[0]->snippet;
                    $contentDetails = $data->items[0]->contentDetails;
                    if ($snippet->liveBroadcastContent != 'none'){
                        abort(403);
                    }else if (!$status->embeddable){
                        abort(403);
                    }else{
                        $newTrack->type = $type;
                        $newTrack->uri = $uri;
                        $newTrack->link = 'http://youtube.com/watch?v=' . $uri;
                        $newTrack->title = $snippet->title;

                        $dateInterval = new DateInterval($contentDetails->duration);
                        $reference = new DateTimeImmutable;
                        $endTime = $reference->add($dateInterval);

                        $newTrack->duration = $endTime->getTimestamp() - $reference->getTimestamp();
                    }
                }else if ($type == 'soundcloud'){
                    $ch = curl_init(
                        'http://api.soundcloud.com/tracks/' . $uri .
                        '?client_id=' . config('services.soundcloud.client_id')
                    );
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $data = curl_exec($ch);
                    if ($data === false){
                        abort(502);
                    }
                    curl_close($ch);
                    $data = json_decode($data);
                    if (!$data->id){
                        dd($data);
                    }
                    if (!$data->streamable || $data->embeddable_by != 'all'){
                        abort(403);
                    }else{
                        $newTrack->type = $type;
                        $newTrack->uri = $uri;
                        $newTrack->link = $data->permalink_url;
                        $newTrack->title = $data->title;
                        $newTrack->artist = $data->user->username;
                        $newTrack->duration = $data->duration / 1000;
                    }
                }else if ($type == 'file'){
                    // not yet implemented
                    abort(501);
                }

                $track = Track::whereType($type)
                    ->whereUri($uri)->first();

                if (!is_null($track)){
                    // keep metadata fresh
                    $track->title = $newTrack->title;
                    $track->artist = $newTrack->artist;
                    $track->album = $newTrack->album;
                }else{
                    $track = $newTrack;
                }

                $track->save();

                $roomState->addTrack($track->id, $track->duration, $user->name);
                RoomState::put($room, $roomState);
            }else{
                abort(403);
            }
        }else{
            abort(403);
        }
    }

    public function removeTrack(Room $room, Request $request){
        if (Auth::check()) {
            $user = $request->user();
            $roomState = RoomState::get($room);

            $key = $request->input('key');

            if (!$roomState->removeTrack($key)){
                abort(403);
            }
            RoomState::put($room, $roomState);
        }else{
            abort(403);
        }
    }

    public function getData (Room $room, Request $request){
        $roomState = RoomState::get($room);
        $data = [];

        if ($request->has('users')){
            $data['users'] = [];
            foreach( $request->input('users') as $userName){
                if (in_array($userName, $roomState->users)){
                    array_push($data['users'], User::whereName($userName)->first());
                }
            }
        }

        if ($request->has('tracks')){
            $data['tracks'] = [];
            foreach( $request->input('tracks') as $trackId){
                if (in_array($trackId, $roomState->queue) || $trackId == $roomState->currentTrack){
                    array_push($data['tracks'], Track::whereId($trackId)->first());
                }
            }
        }

        return $data;
    }
}
