<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name', 'visibility', 'title', 'description', 'current_track_id'
    ];

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function owner(){
        return $this->belongsTo('App\User', 'owner_id');
    }

    public function currentTrack(){
        return $this->belongsTo('App\Track', 'current_track_id');
    }
}
