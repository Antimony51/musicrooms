<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Requests;

class ProfileController extends Controller
{
    public function showOverview($name){
        $user = User::where('name', $name)->first();
        $profile = $user->profile()->first();
        $activeTab = 'overview';
        return view('profile.overview', compact('user', 'profile', 'activeTab'));
    }
}
