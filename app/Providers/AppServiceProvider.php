<?php

namespace App\Providers;

use Validator;
use Illuminate\Support\ServiceProvider;
use App\Room;
use App\RoomState;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('username_chars', function($attribute, $value, $parameters, $validator) {
            return preg_match('/^[a-zA-Z0-9_.-]+$/', $value) === 1;
        });

//        Room::created(function($room){
//            $roomState = new RoomState($room->id);
//            Cache::forever('room_'.$room->id, $roomState);
//        });

        Room::deleted(function($room){
            Cache::forget('room_'.$room->id);
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if($this->app->environment() !== 'production') {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }
    }
}
