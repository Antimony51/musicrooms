@extends('layouts.profile')

@section('tabcontent')
    @foreach($friends as $index => $friend)
        @if($index % 3 == 0)
            <div class="row">
        @endif
                <div class="col-md-4">
                    @include('widgets.usertoken', ['user' => $friend])
                </div>
        @if($index % 3 == 2 || $index == $friends->count()-1)
            </div>
        @endif
    @endforeach
@endsection