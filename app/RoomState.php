<?php

namespace App;

use App\Http\Requests\Request;
use Carbon\Carbon;
use Auth;

class RoomState
{
    public $id;
    public $users = [];
    public $queue = [];
    public $currentTrack = null;
    public $users_timestamp;
    public $queue_timestamp;
    public $currentTrack_timestamp;

    public function __construct($roomId)
    {
        $this->id = $roomId;
        $now = microtime(true);
        $this->users_timestamp = $now;
        $this->queue_timestamp = $now;
        $this->currentTrack_timestamp = $now;
    }

    public function userJoin($userId){
        array_push($this->users, $userId);
        $this->users_timestamp = microtime(true);
    }

    public function userLeave($userId){
        $this->users = array_diff($this->users, [$userId]);
        $this->users_timestamp = microtime(true);
    }

    public function hasTrack($trackId){
        foreach ($this->queue as $track){
            if ($track['trackId'] === $trackId){
                return true;
            }
        }
        return false;
    }

    public function addTrack($trackId, Request $request){
        $ownerId = 0;
        if (Auth::check()){
            $ownerId = $request->user()->id;
        }
        if ( is_null($this->currentTrack) ){
            $this->currentTrack = new TrackState($trackId, $ownerId);
            $this->currentTrack_timestamp = microtime(true);
        } else if (!$this->hasTrack($trackId)) {
            array_push($this->queue, new TrackState($trackId, $ownerId));
            $this->queue_timestamp = microtime(true);
        }else{
            return false;
        }
        return true;
    }

    public function removeTrack($trackId){
        foreach($this->queue as $index => $track){
            if ($track->trackId === $trackId) {
                unset($this->queue[$index]);
            }
        }
        $this->queue = array_values($this->queue);
        $this->queue_timestamp = microtime(true);
    }

    public function advanceQueue(){
        $this->currentTrack = array_shift($this->queue);
        $now = microtime(true);
        $this->currentTrack_timestamp = $now;
        $this->queue_timestamp = $now;
    }
}