<?php

namespace App;

use App\Http\Requests\Request;
use Carbon\Carbon;
use Auth;
use Cache;

class RoomState
{
    public $id;
    public $users = [];
    public $queue = [];
    public $currentTrack = null;
    public $users_timestamp;
    public $queue_timestamp;
    public $currentTrack_timestamp;

    public $trackMeta = [];
    public $userMeta = [];

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
            $state->users_timestamp = microtime(true);
        }

        return $state;
    }

    public static function put(Room $room, $roomState){
        Cache::forever('room_' . $room->id, $roomState);
    }

    public function __construct($roomId)
    {
        $this->id = $roomId;
        $now = microtime(true);
        $this->users_timestamp = $now;
        $this->queue_timestamp = $now;
        $this->currentTrack_timestamp = $now;
    }

    public function hasUser($userName){
        return in_array($userName, $this->users);
    }

    public function userJoin($userName){
        if (!$this->hasUser($userName)) {
            array_push($this->users, $userName);
            $this->users_timestamp = microtime(true);
            $meta = new UserMeta();
            $meta->seen();
            $this->userMeta[$userName] = $meta;
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
            $this->users_timestamp = microtime(true);
        }
    }

    public function userSeen($userName){
        $this->userMeta[$userName]->seen();
    }

    public function hasTrack($trackId){
        return $this->currentTrack == $trackId || in_array($trackId, $this->queue);
    }

    public function addTrack($trackId, Request $request){
        $track = Track::find($trackId);
        if (!is_null($track)){
            $meta = new TrackMeta();
            $meta->duration = $track->duration;
            if (Auth::check()){
                $meta->owner = $request->user()->name;
            }
            if ( is_null($this->currentTrack) ){
                $this->currentTrack = $trackId;
                $this->currentTrack_timestamp = microtime(true);
            } else if (!$this->hasTrack($trackId)) {
                array_push($this->queue, $trackId);
                $this->queue_timestamp = microtime(true);
            }else{
                return false;
            }
            $this->trackMeta[$trackId] = $meta;
            return true;
        }else{
            return false;
        }
    }

    public function removeTrack($trackId){
        $changed = false;
        foreach($this->queue as $index => $id){
            if ($id === $trackId) {
                unset($this->queue[$index]);
                unset($this->trackMeta[$id]);
                $changed = true;
                break;
            }
        }
        if($changed) {
            $this->queue = array_values($this->queue);
            $this->queue_timestamp = microtime(true);
            return true;
        }
        return false;
    }

    public function advanceQueue(){
        if (!in_array($this->currentTrack, $this->queue)){
            unset($this->trackMeta[$this->currentTrack]);
        }
        $this->currentTrack = array_shift($this->queue);
        $now = microtime(true);
        $this->currentTrack_timestamp = $now;
        $this->queue_timestamp = $now;
    }
}
