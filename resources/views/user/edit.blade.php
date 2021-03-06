@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Edit Profile</div>
                    <div class="panel-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ route('updateProfile', ['user' => $user]) }}" enctype="multipart/form-data">
                            {{ csrf_field() }}

                            <div class="form-group{{ $errors->has('cosmetic-name') ? ' has-error' : '' }}">
                                <label for="cosmetic-name" class="col-sm-4 control-label">Cosmetic Name</label>

                                <div class="col-sm-6">
                                    <input id="cosmetic-name" type="text" maxlength="24" class="form-control" name="cosmetic-name" value="{{ old('cosmetic-name', $profile->cosmetic_name) }}">

                                    @if ($errors->has('cosmetic-name'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('cosmetic-name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('icon') ? ' has-error' : '' }}">
                                <label for="icon" class="col-sm-4 control-label">Profile Picture</label>

                                <div class="col-sm-6">
                                    <input class="file-input" type="file" id="icon" name="icon">

                                    @if ($errors->has('icon'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('icon') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group{{ $errors->has('bio') ? ' has-error' : '' }}">
                                <label for="bio" class="col-sm-4 control-label">About Me</label>

                                <div class="col-sm-6">
                                    <textarea id="bio" class="form-control" maxlength="1000" name="bio">{{ old('bio', $profile->bio) }}</textarea>

                                    @if ($errors->has('bio'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('bio') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-6 col-sm-offset-4">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
