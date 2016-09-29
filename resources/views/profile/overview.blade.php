@extends('layouts/profile')

@section('tabcontent')
    @include('widgets.editbuttons')
    <strong>A member for </strong><span>{{ $user->created_at->diffForHumans(null, true) }}</span>
    @if (!empty($profile->bio))
        <h3>About Me</h3>
        <p>
            {{ $profile->bio }}
        </p>
    @endif
@endsection