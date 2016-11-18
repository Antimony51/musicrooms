<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function iconLarge() {
        return $this->icon_large ?: '/img/noprofileimg_large.png';
    }

    public function iconSmall() {
        return $this->icon_small ?: '/img/noprofileimg_small.png';
    }

    public function addPlay() {
        $this->plays++;
    }
}
