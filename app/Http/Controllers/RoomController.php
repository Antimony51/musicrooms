<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Room;
use Auth;
use Cache;

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

    protected function getRoomState(Room $room, Request $request){
        return (array) Cache::get('room_' . $room->id);
    }

    public function syncMe (Room $room, Request $request){
        return $this->getRoomState($room, $request);
    }

    public function join (Room $room, Request $request){
        if (Auth::check()) {
            $roomState = Cache::get('room_' . $room->id);
            $roomState->userJoin($request->user()->id);
            Cache::forever('room_' . $room->id, $roomState);
        }
        return $this->getRoomState($room, $request);
    }

    public function leave (Room $room, Request $request){
        if (Auth::check()) {
            $roomState = Cache::get('room_'. $room->id);
            $roomState->userLeave($request->user()->id);
            Cache::forever('room_' . $room->id, $roomState);
        }
    }
}
