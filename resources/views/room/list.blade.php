@extends('layouts.app')

@section('content')
    <nav>
        <ul class="nav nav-tabs">
            <li class="{{ ($activeTab == 'public') ? 'active' : '' }}"><a href="{{ route('publicRooms') }}">Public Rooms</a></li>
            @if(Auth::check())
                <li class="{{ ($activeTab == 'saved') ? 'active' : '' }}"><a href="{{ route('savedRooms') }}">Saved Rooms</a></li>
                <li class="{{ ($activeTab == 'mine') ? 'active' : '' }}"><a href="{{ route('myRooms') }}">My Rooms</a></li>
                @if(Auth::user()->admin)
                    <li class="{{ ($activeTab == 'all') ? 'active' : '' }}"><a href="{{ route('adminRoomList') }}">All Rooms</a></li>
                @endif
            @endif
        </ul>
    </nav>
    <div class="tab-content panel panel-default tabbed-panel">
        <div class="panel-body">
            @if (!empty($rooms))
                @foreach($rooms as $room)
                    @include('widgets.roomitem')
                @endforeach

                <div class="text-center">{{ $rooms->links() }}</div>
            @else
                {{ $emptyMessage }}
            @endif
        </div>
    </div>
@endsection
