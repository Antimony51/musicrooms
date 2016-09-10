@extends('layouts/master')

@section('title')
    Room - {{ !empty($room->title) ? $room->title : $room->name }}
@endsection

@section('content')
    <h1>Welcome to {{ !empty($room->title) ? $room->title : $room->name }}</h1>
    <h3>This room is owned by {{ $owner->name }}</h3>
@endsection