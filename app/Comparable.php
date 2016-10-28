<?php
namespace App;

trait Comparable {

    protected $comparableField = 'id';

    public function is ($other){
        if (isset($other) && isset($other->{$this->comparableField})) {
            return $this->{$this->comparableField} === $other->{$this->comparableField};
        }else{
            return false;
        }
    }
}
