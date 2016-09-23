<?php
namespace App;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function rooms(){
        return $this->hasMany('App\Room', 'owner_id');
    }

    public function profile(){
        return $this->hasOne('App\Profile', 'user_id');
    }

    public function displayName(){
        return $this->profile()->first()->cosmetic_name ?: $this->name;
    }
}