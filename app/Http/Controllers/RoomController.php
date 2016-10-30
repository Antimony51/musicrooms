<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Room;
use App\User;
use App\Track;
use App\RoomState;
use Auth;

class RoomController extends Controller
{
    public function show(Room $room, Request $request)
    {
        return view('room.main', compact('room'));
    }

    public function showPublicRooms()
    {
        $rooms = Room::whereVisibility('public')->paginate(20);
        $title = "Public Rooms";
        $emptyMessage = "There are no public rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showSavedRooms(Request $request)
    {
        $rooms = $request->user()->savedRooms()->paginate(20);
        $title = "Saved Rooms";
        $emptyMessage = "You have no saved rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showAllRooms()
    {
        $rooms = Room::paginate(20);
        $title = "All Rooms";
        $emptyMessage = "There are no rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function showMyRooms(Request $request)
    {
        $rooms = $request->user()->rooms()->paginate(20);
        $title = "My Rooms";
        $emptyMessage = "You don't own any rooms.";
        return view('room.list', compact('rooms', 'title', 'emptyMessage'));
    }

    public function syncMe (Room $room, Request $request){
        $roomState = RoomState::get($room);
        if (Auth::check()){
            $roomState->userSeen($request->user()->name);
            RoomState::put($room, $roomState);
        }
        return json_encode($roomState);
    }

    public function join (Room $room, Request $request){
        if (Auth::check()) {
            $roomState = RoomState::get($room);
            $roomState->userJoin($request->user()->name);
            RoomState::put($room, $roomState);
        }
        return json_encode(RoomState::get($room));
    }

    public function leave (Room $room, Request $request){
        if (Auth::check()) {
            $roomState = RoomState::get($room);
            $roomState->userLeave($request->user()->name);
            RoomState::put($room, $roomState);
        }
    }

    public function addTrack (Room $room, Request $request){
        if (Auth::check()) {
            $user = $request->user();
            $roomState = RoomState::get($room);
            if ($roomState->hasUser($user)){

            }
        }
    }

    public function getData (Room $room, Request $request){
        $roomState = RoomState::get($room);
        $data = [];

        if ($request->has('users')){
            $data['users'] = [];
            foreach( $request->input('users') as $userName){
                if (in_array($userName, $roomState->users)){
                    array_push($data['users'], User::whereName($userName)->first());
                }
            }
        }

        if ($request->has('tracks')){
            $data['tracks'] = [];
            foreach( $request->input('tracks') as $trackId){
                if (in_array($trackId, $roomState->queue) || $trackId == $roomState->currentTrack){
                    array_push($data['tracks'], Track::whereId($trackId)->first());
                }
            }
        }

        return $data;
    }
}
