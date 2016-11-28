<?php

namespace App;

use App\Http\Requests\Request;
use Carbon\Carbon;
use Cache;
use Log;
use App\Room;
use App\User;

class RoomState {

    private $id;
    private $owner;
    public $users = [];
    public $queue = [];
    public $currentTrack = null;
    public $seek = 0;
    private $currentTrackStart = 0;
    private $currentTrackEnd = 0;
    public $currentTrackMeta = null;
    public $userCount = 0;
    public $roomDataToken;

    public $queueMeta = [];
    private $userMeta = [];

    private $currentTrackChanged = false;
    private $userCountChanged = false;

    public static function get(Room $room){
        $roomState = Cache::rememberForever('room_' . $room->id, function() use ($room){
            return new RoomState($room->id);
        });

        $roomState->update();

        return $roomState;
    }

    public static function put(Room $room, $roomState){
        if ($roomState->userCountChanged){
            $roomState->userCountChanged = false;
            $room->user_count = $roomState->userCount;
            $room->save();
        }
        if ($roomState->currentTrackChanged){
            $roomState->currentTrackChanged = false;
            if (!is_null($roomState->currentTrack)){
                $users = User::whereIn('name', $roomState->users)->get();
                foreach($users as $user){
                    $profile = $user->profile;
                    $profile->addPlay();
                    $profile->save();
                }
            }
        }
        Cache::forever('room_' . $room->id, $roomState);
    }

    public function __construct($roomId)
    {
        $this->id = $roomId;
        $this->owner = Room::findOrFail($roomId)->owner->name;
        $this->roomDataToken = str_random(16);
    }

    public function save(){
        $room = Room::findOrFail($this->id);
        RoomState::put($room, $this);
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
            $this->updateUserCount();
            return true;
        }else{
            return false;
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
            $this->updateUserCount();
        }
    }

    public function clearUsers(){
        $this->users = [];
        $this->userMeta = [];
        $this->updateUserCount();
    }

    public function userSeen($userName){
        if ($this->hasUser($userName)){
            $this->userMeta[$userName]->seen();
        }
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
            $meta->key = $trackId . '.' . str_random(10);
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

    public function removeTrack($key, $userName){
        $queueChanged = false;
        foreach($this->queueMeta as $index => $meta){
            if($meta->key == $key){
                if ($meta->owner == $userName || $userName == $this->owner){
                    unset($this->queue[$index]);
                    unset($this->queueMeta[$index]);
                    $queueChanged = true;
                    break;
                }
            }
        }
        if ($queueChanged){
            $this->queue = array_values($this->queue);
            $this->queueMeta = array_values($this->queueMeta);
            return true;
        }
        return false;
    }

    public function update(){
        $now = microtime(true);
        while($this->currentTrack && $now > $this->currentTrackEnd){
            $this->advanceQueue();
        }
        if ($this->currentTrack){
            $this->seek = $now - $this->currentTrackStart;
        }else{
            $this->seek = 0;
        }

        $usersChanged = false;
        foreach($this->users as $index => $userName){
            if ($this->userMeta[$userName]->expired()){
                unset($this->users[$index]);
                unset($this->userMeta[$userName]);
                $usersChanged = true;
            }
        }
        if ($usersChanged){
            $this->users = array_values($this->users);
            $this->updateUserCount();
        }

        $this->save();
    }

    private function updateUserCount(){
        $newUserCount = sizeof($this->users);
        if ($this->userCount != $newUserCount){
            $this->userCount = $newUserCount;
            $this->userCountChanged = true;
        }
    }

    public function advanceQueue(){
        $this->currentTrack = array_shift($this->queue);
        $this->currentTrackMeta = array_shift($this->queueMeta);
        $this->currentTrackChanged = true;
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
    }
}
