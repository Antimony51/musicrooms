@extends('layouts/app')

@push('scripts_before')
    <script>
        app.currentRoom = {!! $room !!};
    </script>
@endpush

@push('scripts_after')
    <script src="{{ asset('js/room.js') }}"></script>
@endpush
