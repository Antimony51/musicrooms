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
use Log;
use Validator;
use Illuminate\Support\Facades\Input;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;

class RoomController extends Controller
{
    public function show(Room $room, Request $request)
    {
        return view('room.main', compact('room'));
    }

    public function showCreateRoom(Request $request){
        return view('room.create');
    }

    public function createRoom(Request $request){

        $data = collect($request->all())->map(function($item, $key){
            if ($key == 'title' ||
                $key == 'description')
            {
                return trim($item);
            }else{
                return $item;
            }
        })->toArray();
        $validator = $this->validator($data);

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $room = new Room();
        if ($data['visibility'] == 'public'){
            $room->name = $data['name'];
        }else{
            $room->name = str_random(16);
        }
        $room->visibility = $data['visibility'];
        $room->title = $data['title'];
        $room->description = $data['description'];
        $room->owner()->associate($request->user());
        $room->save();

        return redirect(route('room', ['room' => $room]));
    }

    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'visibility' => 'required|in:public,private',
            'name' => 'required_if:visibility,public|username_chars|max:24|unique:rooms,name',
            'title' => 'required|max:24',
            'description' => 'max:1000',
        ]);
        $validator->setAttributeNames([
            'visibility' => '',
            'name' => 'URL',
            'title' => 'title',
            'description' => 'description'
        ]);
        return $validator;
    }

    public function showPublicRooms()
    {
        $rooms = Room::whereVisibility('public')
            ->orderBy('user_count', 'desc')
            ->paginate(10);
        $title = "Public Rooms";
        $emptyMessage = "There are no public rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showSavedRooms(Request $request)
    {
        $rooms = $request->user()->savedRooms()
            ->orderBy('user_count', 'desc')
            ->paginate(10);
        $title = "Saved Rooms";
        $emptyMessage = "You have no saved rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showAllRooms()
    {
        $rooms = Room::orderBy('user_count', 'desc')->paginate(10);
        $title = "All Rooms";
        $emptyMessage = "There are no rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showMyRooms(Request $request)
    {
        $rooms = $request->user()->rooms()
            ->orderBy('user_count', 'desc')
            ->paginate(10);
        $title = "My Rooms";
        $emptyMessage = "You don't own any rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function syncMe (Room $room, Request $request){
        $roomState = RoomState::get($room);
        $roomState->userSeen($request->user()->name);
        $roomState->save();
        return json_encode($roomState);
    }

    public function join (Room $room, Request $request){
        $roomState = RoomState::get($room);
        if (!$roomState->userJoin($request->user()->name)){
            return response('User already in room', 403);
        }
        $roomState->save();
        return json_encode($roomState);
    }

    public function leave (Room $room, Request $request){
        if (Auth::check()) {
            $roomState = RoomState::get($room);
            $roomState->userLeave($request->user()->name);
            $roomState->save();
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
                }else if ($type == 'file' && $request->hasFile('file')){
                    $file = $request->file('file');
                    if (!$file->isValid()){
                        abort(400, "Error while uploading");
                    }
                    $mime = $file->getMimeType();
                    if (!$mime || !preg_match('/^audio\//', $mime)){
                        abort(400, "Invalid format");
                    }
                    $uri = hash_file('sha1', $file->path());

                    $ffmpeg = FFMpeg::create();
                    $ffprobe = $ffmpeg->getFFProbe();
                    $format = $ffprobe->format($file->path());
                    $duration = $format->get('duration');
                    $tags = $format->get('tags');

                    if (is_null($duration)){
                        abort(400, "Could not read duration");
                    }

                    $newTrack->type = $type;
                    $newTrack->uri = $uri;
                    $newTrack->link = "/stream/$uri";
                    $newTrack->duration = $duration;

                    if ($tags){
                        foreach ($tags as $key => $value) {
                            if (preg_match('/^title$/i', $key)){
                                $newTrack->title = $tags[$key];
                            }else if (preg_match('/^artist$/i', $key)){
                                $newTrack->artist = $tags[$key];
                            }else if (preg_match('/^album$/i', $key)){
                                $newTrack->album = $tags[$key];
                            }
                        }
                    }
                }else{
                    abort(400, "Invalid type");
                }

                $track = Track::whereType($type)
                    ->whereUri($uri)->first();

                $isNew = false;

                if (!is_null($track)){
                    // keep metadata fresh
                    $track->title = $newTrack->title;
                    $track->artist = $newTrack->artist;
                    $track->album = $newTrack->album;
                }else{
                    $track = $newTrack;
                    $isNew = true;
                }

                $track->save();

                if ($type == 'file' && $isNew){
                    $audio = $ffmpeg->open($file->path());
                    $outputFormat = new Mp3();
                    $audio->save($outputFormat, storage_path("uploads/audio/$uri.mp3"));
                }

                $roomState->addTrack($track->id, $track->duration, $user->name);
                $roomState->save();

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
            $roomState->save();
        }else{
            abort(403);
        }
    }

    public function getStream ($uri, Request $request){
        $track = Track::whereType('file')
            ->whereUri($uri)->first();
        if (is_null($track)){
            abort(404);
        }
        if ($request->has('room')){
            $roomName = $request->input('room');
            $room = Room::whereName($roomName)->first();
            if ($room){
                $roomState = RoomState::get($room);
                if (!$roomState){
                    abort(500);
                }
                if ($roomState->hasUser($request->user()->name) &&
                    $roomState->currentTrack === $track->id)
                {
                    return response()->file(storage_path("uploads/audio/$uri.mp3"));
                }else{
                    abort(403);
                }
            }else{
                abort(400);
            }
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
