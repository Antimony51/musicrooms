@extends('layouts.profile')

@section('tabcontent')
    @foreach($favorites as $index => $track)
        @if($index % 3 == 0)
            <div class="row">
        @endif
                <div class="col-md-4">
                    @include('widgets.tracktoken')
                </div>
        @if($index % 3 == 2 || $index == $favorites->count()-1)
            </div>
        @endif
    @endforeach
@endsection