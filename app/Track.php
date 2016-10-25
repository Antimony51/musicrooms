<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $visible = [
        'id', 'url', 'title', 'artist', 'album', 'type', 'duration'
    ];

    public function getData(){
        return [
            'id' => $this->id,
            'url' => $this->url,
            'title' => $this->title,
            'artist' => $this->artist,
            'album' => $this->album,
            'type' => $this->type,
            'duration' => $this->duration
        ];
    }
}
