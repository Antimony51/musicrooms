<?php

namespace App;


class UserMeta
{
    private $lastSeen = null;

    public function seen(){
        $this->lastSeen = microtime(true);
    }

    public function expired(){
        return microtime(true) - $this->lastSeen > 10;
    }
}
