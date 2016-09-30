@extends('layouts.profile')

@section('tabcontent')
    @php
        $i = 0;
        $count = $friends->count() + $pending->count();
    @endphp
    @if ($count == 0)
        This user has no friends.
    @else
        @foreach($pending as $pendingFriend)
            @if($i % 3 == 0)
                <div class="row">
            @endif
                <div class="col-md-4">
                    @include('widgets.usertoken', ['user' => $pendingFriend])
                </div>
            @if($i % 3 == 2 || $i == $count-1)
                </div>
            @endif
            @php($i++)
        @endforeach
        @foreach($friends as $friend)
            @if($i % 3 == 0)
                <div class="row">
            @endif
                <div class="col-md-4">
                    @include('widgets.usertoken', ['user' => $friend])
                </div>
            @if($i % 3 == 2 || $i == $count-1)
                </div>
            @endif
            @php($i++)
        @endforeach
    @endif
@endsection