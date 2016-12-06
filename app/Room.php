<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;

class Room extends Model
{
    protected $fillable = [
        'name', 'visibility', 'title', 'description'
    ];

    protected $visible = [
        'name', 'visibility', 'title', 'description', 'owner', 'user_count', 'user_limit', 'user_queue_limit', 'isSaved'
    ];

    protected $appends = [
        'owner', 'isSaved'
    ];

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function owner(){
        return $this->belongsTo('App\User', 'owner_id');
    }

    public function currentTrack(){
        $roomState = RoomState::get($this);
        if (is_null($roomState) || is_null($roomState->currentTrack)){
            return null;
        }else{
            return Track::findOrFail($roomState->currentTrack);
        }
    }

    public function userCount(){
        $roomState = RoomState::get($this);
        if (is_null($roomState)){
            return 0;
        }else{
            return $roomState->userCount;
        }
    }

    public function savedBy(){
        return $this->belongsToMany('App\User', 'saved_rooms');
    }

    public function isSaved(){
        if (Auth::check()){
            return !is_null($this->savedBy()->whereUserId(Auth::user()->id)->first());
        }else{
            return null;
        }
    }
    public function getIsSavedAttribute(){
        return $this->isSaved();
    }

    public function setOwnerAttribute($value){
        $this->owner()->associate($value);
    }

    public function getOwnerAttribute(){
        $owner = $this->owner()->first();
        if ($owner){
            return $owner->makeHidden(['iconSmall', 'iconLarge']);
        }else{
            return null;
        }
    }
}
