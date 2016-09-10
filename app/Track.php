<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    const TYPE_FILE = 0;
    const TYPE_YOUTUBE = 1;
    const TYPE_SOUNDCLOUD = 2;
}
