<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;

class ProfileController extends Controller
{

    public function __construct()
    {

    }

    public function showOverview($name){
        $user = User::where('name', $name)->first();
        $profile = $user->profile;
        $activeTab = 'overview';
        return view('profile.overview', compact('user', 'profile', 'activeTab'));
    }

    public function showFriends($name){
        $user = User::where('name', $name)->first();
        $profile = $user->profile;
        $friends = $user->getFriends();
        $activeTab = 'friends';
        return view('profile.friends', compact('user', 'profile', 'friends', 'activeTab'));
    }

    public function showFavorites($name){
        $user = User::where('name', $name)->first();
        $profile = $user->profile;
        $favorites = $user->favoriteTracks;
        $activeTab = 'favorites';
        return view('profile.favorites', compact('user', 'profile', 'favorites', 'activeTab'));
    }
}
