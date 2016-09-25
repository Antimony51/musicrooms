@extends('layouts/profile')

@section('tabcontent')
    <p>
        {{ $profile->bio }}
    </p>
@endsection