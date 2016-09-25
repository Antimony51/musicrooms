@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            User List
        </div>
        <div class="panel-body">
            <ul>
            @foreach($users as $user)
                <li>
                    <a href="{{ route('profile', ['name' => $user->name]) }}">{{ $user->displayName() }}</a>
                </li>
            @endforeach
            </ul>
        </div>
    </div>
@endsection