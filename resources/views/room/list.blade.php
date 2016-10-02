@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            {{ $title }}
        </div>
        <div class="panel-body">
            @if (!empty($rooms))
                @foreach($rooms as $room)
                    @include('widgets.roomtoken')
                @endforeach

                <div class="text-center">{{ $rooms->links() }}</div>
            @else
                {{ $emptyMessage }}
            @endif
        </div>
    </div>
@endsection