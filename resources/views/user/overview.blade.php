@extends('layouts/profile')

@section('tabcontent')
    {{ csrf_field() }}
    <strong>A member for </strong><span>{{ $user->created_at->diffForHumans(null, true) }}</span>

    @if(!empty($profile->bio))
        <h3>About Me</h3>
        <p>{!! nl2br(htmlentities($profile->bio)) !!}</p>
    @endif
@endsection
