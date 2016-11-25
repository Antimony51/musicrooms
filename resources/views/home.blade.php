@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="jumbotron">
                <h1>Music Rooms</h1>
                <p>Create rooms and fill them with music and friends.</p>
                <p><a href="{{ route('showCreateRoom') }}" class="btn btn-primary">Create a Room</a></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    Join a room
                </div>
                <div class="panel-body">
                    @if (!empty($publicRooms))
                        @foreach($publicRooms as $room)
                            @include('widgets.roomitem')
                        @endforeach
                    @else
                        There are no public rooms.
                    @endif
                    <a href="{{ route('publicRooms') }}">Show more...</a>
                </div>
            </div>
        </div>
    </div>
    @if(Auth::check())
        <div class="row">
            <div class="col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        My Rooms
                    </div>
                    <div class="panel-body">
                        @if (!empty($myRooms))
                            @foreach($myRooms as $room)
                                @include('widgets.roomitem')
                            @endforeach
                        @else
                            You don't own any rooms.
                        @endif
                        <a href="{{ route('myRooms') }}">Show more...</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Saved Rooms
                    </div>
                    <div class="panel-body">
                        @if (!empty($savedRooms))
                            @foreach($savedRooms as $room)
                                @include('widgets.roomitem')
                            @endforeach
                        @else
                            You have not saved any rooms.
                        @endif
                        <a href="{{ route('savedRooms') }}">Show more...</a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
