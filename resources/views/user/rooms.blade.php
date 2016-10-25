@extends('layouts.profile')

@section('tabcontent')
    @forelse($rooms as $room)
        @include('widgets.roomtoken')
    @empty
        This user has no public rooms.
    @endforelse
@endsection