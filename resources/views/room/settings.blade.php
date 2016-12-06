@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Room Settings</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('updateRoom', ['room' => $room]) }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('visibility') ? ' has-error' : '' }}">
                            <label for="visibility" class="col-sm-4 control-label">Visibility</label>

                            <div class="col-sm-6">
                                <select class="form-control" id="visibility" name="visibility" >
                                    <option value="public" {{ old('visibility', $room->visibility) == 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="private" {{ old('visibility', $room->visibility) == 'private' ? 'selected' : '' }}>Private</option>
                                </select>

                                @if ($errors->has('visibility'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('visibility') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-sm-4 control-label">URL</label>

                            <div class="col-sm-6">
                                <div>
                                <input id="name" type="text" maxlength="24" class="form-control" value="{{ old('name', $room->name) }}" autocomplete="off">
                                <input type="hidden" name="name" value="{{ old('name', $room->name) }}">
                                <span class="help-block text-muted hidden url-preview">{{ url('/room') . '/'}}<strong class="value"></strong></span>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('title') ? ' has-error' : '' }}">
                            <label for="title" class="col-sm-4 control-label">Title</label>

                            <div class="col-sm-6">
                                <input id="title" type="text" maxlength="40" class="form-control" name="title" value="{{ old('title', $room->title) }}" autocomplete="off">

                                @if ($errors->has('title'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('description') ? ' has-error' : '' }}">
                            <label for="description" class="col-sm-4 control-label">Description</label>

                            <div class="col-sm-6">
                                <textarea id="description" class="form-control" maxlength="1000" name="description">{{ old('description', $room->description) }}</textarea>

                                @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('user_limit') ? ' has-error' : '' }}">
                            <label for="user_limit" class="col-sm-4 control-label">User Limit (0=unlimited)</label>

                            <div class="col-sm-6">
                                <input id="user_limit" type="number" class="form-control" name="user_limit" value="{{ old('user_limit', $room->user_limit) }}" autocomplete="off">

                                @if ($errors->has('user_limit'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('user_limit') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('user_queue_limit') ? ' has-error' : '' }}">
                            <label for="user_queue_limit" class="col-sm-4 control-label">Queued Tracks Per User (0=unlimited)</label>

                            <div class="col-sm-6">
                                <input id="user_queue_limit" type="number" class="form-control" name="user_queue_limit" value="{{ old('user_queue_limit', $room->user_queue_limit) }}" autocomplete="off">

                                @if ($errors->has('user_queue_limit'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('user_queue_limit') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('owner') ? ' has-error' : '' }}">
                            <label for="owner" class="col-sm-4 control-label">Owner</label>

                            <div class="col-sm-6">
                                <input id="owner" type="text" maxlength="24" class="form-control" name="owner" value="{{ old('owner', $room->owner ? $room->owner->name : '') }}" placeholder="Nobody" autocomplete="off">

                                @if ($errors->has('owner'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('owner') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                    <hr/>
                    <form id="delete-room" class="form-horizontal" role="form" method="POST" action="{{ route('deleteRoom', ['room' => $room]) }}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-4">
                                <button type="submit" class="btn btn-danger">
                                    Delete Room
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts_after')
    <script src="{{ url('/js/url-preview.js') }}"></script>
    <script>
        $('#name').one('focus', function(){
            alertify.alert('Room URL', 'Changing the room URL will cause any users currently connected to your room to disconnect.');
        });

        $('#delete-room').on('submit', function(ev){
            ev.preventDefault();
            alertify.confirm('Delete Room', 'Are you sure you want to delete the room? This action cannot be undone.', function(){
                ev.target.submit();
            }, function() {});
        });
    </script>
@endpush
