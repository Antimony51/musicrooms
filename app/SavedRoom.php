<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SavedRoom extends Model
{
    protected $fillable = [
        'user_id', 'room_id',
    ];
}
