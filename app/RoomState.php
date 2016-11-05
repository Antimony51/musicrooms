<?php

namespace App;

use App\Http\Requests\Request;
use Carbon\Carbon;
use Auth;
use Cache;

class RoomState {

    private $id;
    public $users = [];
    public $queue = [];
    public $currentTrack = null;
    public $seek = 0;
    private $currentTrackStart = 0;
    private $currentTrackEnd = 0;
    public $currentTrackMeta = null;

    public $queueMeta = [];
    private $userMeta = [];
    private $shouldUpdateDb = false;

    public static function get(Room $room){
        $state = Cache::rememberForever('room_' . $room->id, function() use ($room){
            return new RoomState($room->id);
        });

        $usersChanged = false;
        foreach($state->users as $index => $userName){
            if ($state->userMeta[$userName]->expired()){
                unset($state->users[$index]);
                unset($state->userMeta[$userName]);
                $usersChanged = true;
            }
        }
        if ($usersChanged){
            $state->users = array_values($state->users);
        }

        return $state;
    }

    public static function put(Room $room, $roomState){
        $updateDb = $roomState->shouldUpdateDb;
        $roomState->shouldUpdateDb = false;
        Cache::forever('room_' . $room->id, $roomState);
        if ($updateDb){
            $room->current_track_id = $roomState->currentTrack;
            $room->save();
        }
    }

    public function __construct($roomId)
    {
        $this->id = $roomId;
    }

    public function hasUser($userName){
        return in_array($userName, $this->users);
    }

    public function userJoin($userName){
        if (!$this->hasUser($userName)) {
            array_push($this->users, $userName);
            $meta = new UserMeta();
            $meta->seen();
            $this->userMeta[$userName] = $meta;
        }else{
            abort(403);
        }
    }

    public function userLeave($userName){
        $changed = false;
        foreach($this->users as $index => $name){
            if ($name == $userName){
                unset($this->users[$index]);
                unset($this->userMeta[$userName]);
                $changed = true;
                break;
            }
        }
        if($changed) {
            $this->users = array_values($this->users);
        }
    }

    public function userSeen($userName){
        $this->userMeta[$userName]->seen();
    }

    public function hasTrack($trackId){
        return $this->currentTrack == $trackId || in_array($trackId, $this->queue);
    }

    public function addTrack($trackId, $duration, $ownerName){
        $track = Track::find($trackId);
        if (!is_null($track)){
            $meta = new TrackMeta();
            $meta->owner = $ownerName;
            $meta->duration = $duration;
            $meta->key = uniqid($trackId . '.', true);
            array_push($this->queue, $trackId);
            array_push($this->queueMeta, $meta);
            if (is_null($this->currentTrack)){
                $this->advanceQueue();
            }
            return true;
        }else{
            return false;
        }
    }

    public function removeTrack($key){
        $queueChanged = false;
        foreach($this->queueMeta as $index => $meta){
            if($meta->key == $key){
                unset($this->queue[$index]);
                unset($this->queueMeta[$index]);
                $queueChanged = true;
            }
        }
        if ($queueChanged){
            $this->queue = array_values($this->queue);
            $this->queueMeta = array_values($this->queueMeta);
            return true;
        }
        return false;
    }

    public function updateState(){
        $now = microtime(true);
        while($this->currentTrack && $now > $this->currentTrackEnd){
            $this->advanceQueue();
        }
        if ($this->currentTrack){
            $this->seek = $now - $this->currentTrackStart;
        }else{
            $this->seek = 0;
        }
    }

    public function advanceQueue(){
        $this->currentTrack = array_shift($this->queue);
        $this->currentTrackMeta = array_shift($this->queueMeta);
        if ($this->currentTrack){
            $duration = $this->currentTrackMeta->duration;
            $this->currentTrackStart = microtime(true);
            $this->currentTrackEnd = $this->currentTrackStart + $duration;
            $this->seek = 0;
        }else{
            $this->currentTrackStart = 0;
            $this->currentTrackEnd = 0;
            $this->seek = 0;
        }
        $this->shouldUpdateDb = true;
    }
}
