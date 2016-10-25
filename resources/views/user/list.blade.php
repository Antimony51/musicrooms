@extends('layouts.app')

@section('content')
    <div class="panel panel-default">
        <div class="panel-heading">
            User List
        </div>
        <div class="panel-body">
            @foreach($users as $user)
                @include('widgets.usertoken')
            @endforeach

            <div class="text-center">{{ $users->links() }}</div>
        </div>
    </div>
@endsection