<?php
namespace App;
use Hootlex\Friendships\Traits\Friendable;
use Illuminate\Foundation\Auth\User as Authenticatable;
class User extends Authenticatable
{
    use Friendable, Comparable;

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

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'name';
    }

    public function rooms(){
        return $this->hasMany('App\Room', 'owner_id');
    }

    public function profile(){
        return $this->hasOne('App\Profile', 'user_id');
    }

    public function displayName(){
        return $this->profile()->first()->cosmetic_name ?: $this->name;
    }

    public function favoriteTracks(){
        return $this->belongsToMany('App\Track', 'favorites');
    }
}