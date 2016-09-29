<?php
namespace App;

trait Comparable {
    public function is ($other){
        if (isset($other) && isset($other->id)) {
            return $this->id === $other->id;
        }else{
            return false;
        }
    }
}