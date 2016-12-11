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
use FFMpeg\Exception\RuntimeException;
use Storage;

class RoomController extends Controller
{
    public function show(Room $room, Request $request)
    {
        return view('room.main', compact('room'));
    }

    public function showCreateRoom(){
        do{
            $suggestedName = str_random(16);
        }while(Room::whereName($suggestedName)->count() > 0);

        return view('room.create', compact('suggestedName'));
    }

    public function showRoomSettings(Room $room){
        return view('room.settings', compact('room'));
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
        $validator = Validator::make($data, [
            'visibility' => 'required|in:public,private',
            'name' => 'required|username_chars|max:24|unique:rooms,name',
            'title' => 'required|max:40',
            'description' => 'max:1000',
        ]);
        $validator->setAttributeNames([
            'visibility' => 'visibility',
            'name' => 'URL',
            'title' => 'title',
            'description' => 'description'
        ]);

        if ($validator->fails()) {
            $this->throwValidationException(
                $request, $validator
            );
        }

        $room = new Room();
        $room->name = $data['name'];
        $room->visibility = $data['visibility'];
        $room->title = $data['title'];
        $room->description = $data['description'];
        $room->owner()->associate($request->user());
        $room->save();

        return redirect(route('room', ['room' => $room]));
    }

    public function updateRoom(Room $room, Request $request){
        $user = $request->user();
        if ($user->is($room->owner) || $user->admin){
            $data = collect($request->all())
                ->map(function($item, $key){
                    switch($key){
                        case 'title':
                        case 'description':
                        case 'owner':
                            return trim($item);
                        default:
                            return $item;
                    }
                })->filter(function($item, $key) use ($room){
                    switch($key){
                        case 'visibility':
                        case 'name':
                        case 'title':
                        case 'description':
                        case 'user_limit':
                        case 'user_queue_limit':
                            return $room->{$key} != $item;
                        case 'owner':
                            if ($room->owner){
                                return $room->owner->name != $item;
                            }else{
                                return true;
                            }
                    }
                })->toArray();

            $validator = Validator::make($data, [
                'visibility' => 'in:public,private',
                'name' => 'username_chars|max:24|unique:rooms,name',
                'title' => 'max:40',
                'description' => 'max:1000',
                'user_limit' => 'integer|min:0',
                'user_queue_limit' => 'integer|min:0',
                'owner' => 'exists:users,name'
            ]);
            $validator->setAttributeNames([
                'visibility' => 'visibility',
                'name' => 'URL',
                'title' => 'title',
                'description' => 'description',
                'user_limit' => 'user limit',
                'user_queue_limit' => 'queued tracks per user',
                'owner' => 'owner'
            ]);

            if ($validator->fails()) {
                $this->throwValidationException(
                    $request, $validator
                );
            }

            $roomState = RoomState::get($room);

            if (isset($data['name'])){
                $room->name = $data['name'];
                $roomState->clearUsers();
            }
            if (isset($data['visibility'])) $room->visibility = $data['visibility'];
            if (isset($data['title'])) $room->title = $data['title'];
            if (isset($data['description'])) $room->description = $data['description'];
            if (isset($data['user_limit'])) $room->user_limit = $data['user_limit'];
            if (isset($data['user_queue_limit'])) $room->user_queue_limit = $data['user_queue_limit'];
            if (isset($data['owner'])) $room->owner = !empty($data['owner']) ? User::whereName($data['owner'])->first() : null;
            $room->save();

            $roomState->invalidateRoomData();
            $roomState->save();

            return redirect(route('room', ['room' => $room]));
        }else{
            abort(403, "You are not the room owner.");
        }
    }

    public function deleteRoom(Room $room, Request $request){
        $user = $request->user();
        if ($user->is($room->owner) || $user->admin){
            $room->delete();

            return redirect(route('home'));
        }else{
            abort(403, "You are not the room owner.");
        }
    }

    public function showPublicRooms()
    {
        $rooms = Room::whereVisibility('public')
            ->orderBy('user_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $activeTab = "public";
        $emptyMessage = "There are no public rooms.";
        return view('room.list', compact('rooms', 'activeTab', 'emptyMessage'));
    }

    public function showSavedRooms(Request $request)
    {
        $rooms = $request->user()->savedRooms()
            ->orderBy('user_count', 'desc')
            ->orderBy('saved_rooms.created_at', 'desc')
            ->paginate(10);
        $activeTab = "saved";
        $emptyMessage = "You have no saved rooms.";
        return view('room.list', compact('rooms', 'activeTab', 'emptyMessage'));
    }

    public function showAllRooms()
    {
        $rooms = Room::orderBy('user_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $activeTab = "all";
        $emptyMessage = "There are no rooms.";
        return view('room.list', compact('rooms', 'activeTab', 'emptyMessage'));
    }

    public function showMyRooms(Request $request)
    {
        $rooms = $request->user()->rooms()
            ->orderBy('user_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        $activeTab = "mine";
        $emptyMessage = "You don't own any rooms.";
        return view('room.list', compact('rooms', 'activeTab', 'emptyMessage'));
    }

    public function syncMe (Room $room, Request $request){
        $roomState = RoomState::get($room);
        $roomState->userSeen($request->user()->name);
        $roomState->save();
        return json_encode($roomState);
    }

    public function join (Room $room, Request $request){
        $roomState = RoomState::get($room);
        if ($room->user_limit > 0 && $roomState->userCount >= $room->user_limit){
            return response('This room is full.', 403);
        }
        if (!$roomState->userJoin($request->user()->name)){
            return response('You are already in this room.', 403);
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
        $user = $request->user();
        $roomState = RoomState::get($room);

        if ($room->user_queue_limit > 0){
            $userQueued = 0;
            foreach ($roomState->queueMeta as $trackMeta) {
                if ($trackMeta->owner == $user->name){
                    $userQueued++;
                }
            }
            if ($userQueued >= $room->user_queue_limit){
                return response('You cannot have more than '.$room->user_queue_limit.' tracks in the queue.', 403);
            }
        }

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
                    return response("Error while communicating with YouTube: ".curl_error($ch), 502);
                }
                curl_close($ch);
                $data = json_decode($data);
                if (sizeof($data->items) == 0){
                    return response('Video does not exist.', 404);
                }
                $status = $data->items[0]->status;
                $snippet = $data->items[0]->snippet;
                $contentDetails = $data->items[0]->contentDetails;
                if ($snippet->liveBroadcastContent != 'none'){
                    return response("Can't add a live broadcast.", 403);
                }else if (!$status->embeddable){
                    return response("Track is not embeddable.", 403);
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
                    return response("Error while communicating with Soundcloud: ".curl_error($ch), 502);
                }
                curl_close($ch);
                $data = json_decode($data);
                if (!$data->streamable || $data->embeddable_by != 'all'){
                    return response("Track is not embeddable.", 403);
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
                    return response("Error while uploading.", 400);
                }
                $mime = $file->getMimeType();
                if (!$mime || !preg_match('/^audio\//', $mime)){
                    return response("Invalid format.", 400);
                }
                $uri = hash_file('sha1', $file->path());

                $ffmpeg = FFMpeg::create();
                $ffprobe = $ffmpeg->getFFProbe();
                $format = $ffprobe->format($file->path());
                $duration = $format->get('duration');
                $tags = $format->get('tags');

                if (is_null($duration)){
                    return response("Could not read duration", 400);
                }

                $newTrack->type = $type;
                $newTrack->uri = $uri;
                $fileLink = "uploads/audio/$uri.mp3";
                $newTrack->link = $fileLink;
                $newTrack->duration = $duration;

                if ($tags){
                    foreach ($tags as $key => $value) {
                        $tag = !empty($tags[$key]) ? explode(';', $tags[$key])[0] : null;
                        if (!is_null($tag)){
                            if (preg_match('/^title$/i', $key)){
                                $newTrack->title = $tag;
                            }else if (preg_match('/^artist$/i', $key)){
                                $newTrack->artist = $tag;
                            }else if (preg_match('/^album$/i', $key)){
                                $newTrack->album = $tag;
                            }
                        }
                    }
                }
            }else if ($type == 'file' && !is_null($uri)){
                $newTrack = null;
            }else{
                return response("Invalid type.", 400);
            }

            $track = Track::whereType($type)
                ->whereUri($uri)->first();

            $isNew = false;

            if (!is_null($newTrack)){
                if (!is_null($track)){
                    // keep metadata fresh
                    $track->title = $newTrack->title;
                    $track->artist = $newTrack->artist;
                    $track->album = $newTrack->album;

                    if ($type == 'file' && !Storage::cloud()->exists($track->link)){
                        Log::warning("Missing track file $uri.mp3, will transcode again.");
                        $isNew = true;
                    }
                }else{
                    $track = $newTrack;
                    $isNew = true;
                }
            }else{
                if (is_null($track)){
                    return response("Track not found.", 404);
                }
            }

            $track->save();

            if ($type == 'file' && $isNew){
                set_time_limit(300);
                $audio = $ffmpeg->open($file->path());
                $outputFormat = new Mp3();
                $tmpFile = "audio/$uri.mp3";
                $tmpFolder = config('filesystems.disks.temp.root')."/audio";
                if (!file_exists($tmpFolder)){
                    mkdir($tmpFolder, 0777, true);
                }
                try{
                    $audio->save($outputFormat, config('filesystems.disks.temp.root')."/$tmpFile");
                }catch(RuntimeException $e){
                    return response($e->getMessage(), 500);
                }

                Storage::cloud()->put($fileLink, Storage::disk('temp')->get($tmpFile), 'public');
                Storage::disk('temp')->delete($tmpFile);
            }

            // the current state may have changed during transfer/encoding, so reload it before saving.
            $roomState = RoomState::get($room);
            $roomState->addTrack($track->id, $track->duration, $user->name);
            $roomState->save();

        }else{
            return response("You are not in the room.", 403);
        }
    }

    public function removeTrack(Room $room, Request $request){
        $user = $request->user();
        $roomState = RoomState::get($room);

        $key = $request->input('key');

        if (!$roomState->removeTrack($key, $user->admin ? null : $user->name)){
            return response("You don't have permission to remove this track.",403);
        }
        $roomState->save();
    }

    public function skipCurrentTrack(Room $room, Request $request){
        $roomState = RoomState::get($room);
        $user = $request->user();
        if ($roomState->advanceQueue($user->admin ? null : $user->name)){
            $roomState->save();
            return response("", 200);
        }else{
            return response("You don't have permission to skip the current track.",403);
        }
    }

    public function seekTo(Room $room, Request $request){
        $roomState = RoomState::get($room);
        $user = $request->user();
        if ($request->has('pos')){
            if ($roomState->seekTo(floatval($request->input('pos')), $user->admin ? null : $user->name)){
                $roomState->save();
                return response("", 200);
            }else{
                return response("You don't have permission to seek the current track.",403);
            }
        }else{
            return response("Position parameter missing.", 400);
        }
    }

    public function clearQueue(Room $room, Request $request){
        $roomState = RoomState::get($room);
        $user = $request->user();
        if ($roomState->clearQueue($user->admin ? null : $user->name)){
            $roomState->save();
            return response("", 200);
        }else{
            return response("You don't have permission to clear the queue.",403);
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
