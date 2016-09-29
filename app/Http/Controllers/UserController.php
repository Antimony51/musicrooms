<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;
use Auth;

class UserController extends Controller
{

    public function __construct()
    {

    }

    public function showOverview(User $user, Request $request){
        $profile = $user->profile;
        $activeTab = 'overview';
        $ownProfile = $user->is($request->user());
        return view('profile.overview', compact('user', 'profile', 'activeTab', 'ownProfile'));
    }

    public function showFriends(User $user, Request $request){
        $profile = $user->profile;
        $friends = $user->getFriends();
        $activeTab = 'friends';
        $ownProfile = $user->is($request->user());
        $pending = collect();
        if ($ownProfile){
            $pending = $user->getPendingFriendships()->map(function($friendship) use($user){
                if ($friendship->sender->is($user)){
                    return $friendship->recipient;
                }else{
                    return $friendship->sender;
                }
            });
        }
        return view('profile.friends', compact('user', 'profile', 'friends', 'pending', 'activeTab', 'ownProfile'));
    }

    public function showFavorites(User $user, Request $request){
        $profile = $user->profile;
        $favorites = $user->favoriteTracks;
        $mutualFavorites = null;
        $activeTab = 'favorites';
        $ownProfile = $user->is($request->user());
        if ($ownProfile){
            $mutualFavorites = $favorites;
        }elseif (Auth::check()){
            $mutualFavorites = $favorites->intersect(Auth::user()->favoriteTracks);
        }
        return view('profile.favorites', compact('user', 'profile', 'favorites', 'mutualFavorites', 'activeTab', 'ownProfile'));
    }

    public function addFavorite(User $user, $id, Request $request){
        if ($user->is($request->user())) {
            $user->favoriteTracks()->attach($id);
        }
    }

    public function removeFavorite(User $user, $id, Request $request){
        if ($user->is($request->user())) {
            $user->favoriteTracks()->detach($id);
        }
    }

    public function updateName(User $user, $newName, Request $request){

    }

    public function updateCosmeticName(User $user, $newCosmeticName, Request $request){

    }

    public function updateBio(User $user, $newBio, Request $request){

    }

    public function addFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->befriend($user);
        }
    }

    public function removeFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->unfriend($user);
        }
    }

    public function acceptFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->acceptFriendRequest($user);
        }
    }

    public function declineFriend(User $user, Request $request){
        if(!$user->is($request->user())){
            $request->user()->denyFriendRequest($user);
        }
    }
}
