<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Room;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $publicRooms = Room::whereVisibility('public')
            ->orderBy('user_count', 'desc')
            ->limit(3)->get();
        $myRooms = null;
        $savedRooms = null;
        if (Auth::check()){
            $myRooms = $request->user()->rooms()
                ->orderBy('user_count', 'desc')
                ->limit(3)->get();
            $savedRooms = $request->user()->savedRooms()
                ->orderBy('user_count', 'desc')
                ->limit(3)->get();
        }
        return view('home', compact('publicRooms', 'myRooms', 'savedRooms'));
    }
}
