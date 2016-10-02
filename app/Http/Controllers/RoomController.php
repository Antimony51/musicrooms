<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Room;
use Auth;

class RoomController extends Controller
{
    public function showRoom(Room $room)
    {
        $owner = $room->owner()->first();
        return view('room.main', compact('room', 'owner'));
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
}
