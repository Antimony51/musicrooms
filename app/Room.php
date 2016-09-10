<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'name', 'visibility'
    ];

    public function owner(){
        return $this->belongsTo('App\User', 'owner_id');
    }
}
