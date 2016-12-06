<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Storage;

class Track extends Model
{
    protected $visible = [
        'id', 'uri', 'link', 'title', 'artist', 'album', 'type', 'duration', 'isFaved'
    ];

    protected $appends = [
        'isFaved'
    ];

    public function favedBy(){
        return $this->belongsToMany('App\User', 'favorites');
    }

    public function isFaved(){
        if (Auth::check()){
            return !is_null($this->favedBy()->whereUserId(Auth::user()->id)->first());
        }else{
            return null;
        }
    }

    public function getIsFavedAttribute(){
        return $this->isFaved();
    }

    public function getLinkAttribute($value){
        if ($this->type == 'file'){
            return Storage::cloud()->url($value);
        }
        return $value;
    }
}
