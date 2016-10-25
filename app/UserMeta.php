<?php

namespace App;


class UserMeta
{
    public $lastSeen = null;

    public function seen(){
        $this->lastSeen = microtime(true);
    }

    public function expired(){
        return microtime(true) - $this->lastSeen > 10;
    }
}