@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Create Room</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('createRoom') }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('visibility') ? ' has-error' : '' }}">
                            <label for="visibility" class="col-sm-4 control-label">Visibility</label>

                            <div class="col-sm-6">
                                <select class="form-control" id="visibility" name="visibility" >
                                    <option value="public" {{ old('visibility', 'public') == 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="private" {{ old('visibility', 'public') == 'private' ? 'selected' : '' }}>Private</option>
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
                                <input id="name" type="text" class="form-control" value="{{ old('name', $suggestedName) }}" autocomplete="off">
                                <input type="hidden" name="name" value="{{ old('name') }}">
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
                                <input id="title" type="text" class="form-control" name="title" value="{{ old('title') }}" autocomplete="off">

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
                                <textarea id="description" type="text" class="form-control" name="description">{{ old('description') }}</textarea>

                                @if ($errors->has('description'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Create
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
@endpush
