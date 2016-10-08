<?php
/**
 * Created by PhpStorm.
 * User: mdick
 * Date: 10/7/2016
 * Time: 10:00 AM
 */

namespace App;


class TrackState
{
    public $trackId;
    public $ownerId;

    public function __construct($trackId, $ownerId){
        $this->trackId = $trackId;
        $this->ownerId = $ownerId;
    }
}