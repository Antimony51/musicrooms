@extends('layouts.profile')

@section('tabcontent')
    @forelse($rooms as $index => $room)

    @empty
        This user has no public rooms.
    @endforelse
@endsection