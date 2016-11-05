<?php

namespace App;

use JsonSerializable;

class TrackMeta implements JsonSerializable
{
    public $owner = null;
    public $duration = null;
    public $key = null;

    public function JsonSerialize () {
        return [
            'owner' => $this->owner,
            'key' => $this->key
        ];
    }
}
