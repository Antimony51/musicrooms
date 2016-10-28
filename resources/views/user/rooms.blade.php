@extends('layouts.profile')

@section('tabcontent')
    @forelse($rooms as $room)
        @include('widgets.roomitem')
    @empty
        This user has no public rooms.
    @endforelse
@endsection
