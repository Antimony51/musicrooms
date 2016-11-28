<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Storage;

class Profile extends Model
{
    public function user() {
        return $this->belongsTo('App\User');
    }

    public function iconLarge() {
        return $this->icon_large ? Storage::cloud()->url($this->icon_large) : asset('/img/noprofileimg_large.png');
    }

    public function iconSmall() {
        return $this->icon_small ? Storage::cloud()->url($this->icon_small) : asset('/img/noprofileimg_small.png');
    }

    public function addPlay() {
        $this->plays++;
    }
}
