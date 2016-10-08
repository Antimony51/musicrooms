@extends('layouts/app')

@push('scripts_after')
    <script>
        var userData = {};
        var trackData = {};
        var roomState = null;

        const TRACK_TOKEN =
            '<div class="panel panel-default">' +
                '<div class="panel-body">' +
                    '<span class="title"></span> - <span class="artist"></span><br>' +
                    '<span class="></span>' +
                '</div>' +
            '</div>';

        function processStateChange (newState){
            var usersAdded = [];
            var queueAdded = [];
            if (!roomState){
                roomState = newState;
            }else{
                if (newState.users_timestamp != roomState.users_timestamp){
                    usersAdded = newState.users.filter((item) => {
                        return !roomState.users.has(item);
                    });
                }

                if (newState.queue_timestamp != roomState.queue_timestamp){
                    queueAdded = newState.queue.filter((item) => {
                        return !roomState.queue.has(item);
                    });
                }


            }
        }

        function roomInit() {
            $.ajax({
                'url': '{{ route('joinRoom', ['room' => $room]) }}',
                'method': 'get',
                'dataType': 'json'
            })
                .done(function (data) {
                    processStateChange(data);
                })
                .fail(function(){
                    setTimeout(roomInit, 250);
                });
        }

        roomInit();
    </script>
@endpush

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body player-panel">
                        adsf<br>
                        asdfasdf<br>
                        asdf
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body queue-panel">
                        QUEUE
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body chat-panel">
                        CHAT
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body users-panel">
                        USERS
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection