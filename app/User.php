<?php
namespace App;
use Hootlex\Friendships\Traits\Friendable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

class User extends Authenticatable
{
    use Friendable, Comparable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'questions', 'answers'
    ];

    protected $visible = [
        'name', 'displayName', 'iconSmall', 'iconLarge', 'friendStatus', 'admin',
    ];

    protected $appends = [
        'displayName', 'iconSmall', 'iconLarge', 'friendStatus'
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
        return $this->belongsToMany('App\Track', 'favorites')->withTimestamps();
    }

    public function savedRooms(){
        return $this->belongsToMany('App\Room', 'saved_rooms')->withTimestamps();
    }

    public function iconSmall(){
        return $this->profile->iconSmall();
    }

    public function iconLarge(){
        return $this->profile->iconLarge();
    }

    public function friendStatus(){
        $user = Auth::user();
        if (Auth::check() && !$this->is($user)){
            if ($this->isFriendWith($user)){
                return 'friend';
            }else if ($this->hasFriendRequestFrom($user)){
                return 'request_sent';
            }else if ($this->hasSentFriendRequestTo($user)){
                return 'request_received';
            }else{
                return 'can_add';
            }
        }else{
            return null;
        }
    }

    public function getDisplayNameAttribute(){
        return $this->displayName();
    }

    public function getIconSmallAttribute(){
        return $this->iconSmall();
    }

    public function getIconLargeAttribute(){
        return $this->iconLarge();
    }

    public function getFriendStatusAttribute(){
        return $this->friendStatus();
    }

    public function getQuestionsAttribute($value){
        return json_decode($value);
    }

    public function setQuestionsAttribute($value){
        $this->attributes['questions'] = json_encode($value);
    }

    public function getAnswersAttribute($value){
        return json_decode($value);
    }

    public function setAnswersAttribute($value){
        $this->attributes['answers'] = json_encode($value);
    }
}
