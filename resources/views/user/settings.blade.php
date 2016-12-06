@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">User Settings</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('updateUser', ['user' => $user]) }}">
                        {{ csrf_field() }}

                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-sm-4 control-label">Username</label>

                            <div class="col-sm-6">
                                <input id="name" type="text" maxlength="24" class="form-control" name="name" value="{{ old('name', $user->name) }}">

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-sm-4 control-label">E-Mail Address</label>

                            <div class="col-sm-6">
                                <input id="email" type="email" maxlength="255" class="form-control" name="email" value="{{ old('email', $user->email) }}">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-sm-4 control-label">Password</label>

                            <div class="col-sm-6">
                                <input id="password" type="password" maxlength="255" class="form-control" name="password" placeholder="unchanged">

                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <label for="password-confirm" class="col-sm-4 control-label">Confirm Password</label>

                            <div class="col-sm-6">
                                <input id="password-confirm" type="password" maxlength="255" class="form-control" name="password_confirmation" placeholder="unchanged">

                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if (Auth::user()->admin)
                            <div class="form-group{{ $errors->has('admin') ? ' has-error' : '' }}">
                                <label for="admin" class="col-sm-4 control-label">Admin</label>

                                <div class="col-sm-6">
                                    <input id="admin" type="checkbox" style="vertical-align: middle;" name="admin" {{ old('admin', $user->admin) ? 'checked' : '' }}>

                                    @if ($errors->has('admin'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('admin') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if (config('auth.passwords.users.use_security_questions'))
                            <div class="{{ $errors->has("questions") || $errors->has("answers") ? ' has-error' : '' }}">
                                <div class="form-group">
                                    <h4 class="col-sm-4 control-label">Security Questions:</h4>
                                </div>

                                <div class="form-group">
                                    @if ($errors->has("questions") || $errors->has("answers"))
                                        <div class="help-block col-sm-6 col-sm-offset-4">
                                            <strong>You must provide {{ config('auth.passwords.users.num_security_questions') }} security questions and answers.</strong>
                                        </div>
                                    @endif
                                </div>

                                @for ($i=0; $i < config('auth.passwords.users.num_security_questions'); $i++)
                                    @if($i != 0)
                                        <br>
                                    @endif

                                    <div class="form-group{{ $errors->has("questions.$i") ? ' has-error' : '' }}">
                                        <label for="question{{$i}}" class="col-sm-4 control-label">Question {{$i+1}}</label>

                                        <div class="col-sm-6">
                                            <input id="question{{$i}}" type="text" maxlength="255" class="form-control" autocomplete="off"
                                                name="questions[{{$i}}]" value="{{ old("questions.$i", $user->questions[$i]) }}">

                                            @if ($errors->has("questions.$i"))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first("questions.$i") }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group{{ $errors->has("answers.$i") ? ' has-error' : '' }}">
                                        <label for="answer{{$i}}" class="col-sm-4 control-label">Answer {{$i+1}}</label>

                                        <div class="col-sm-6">
                                            <input id="answer{{$i}}" type="text" maxlength="255" class="form-control" placeholder="unchanged" autocomplete="off"
                                                name="answers[{{$i}}]" value="{{ old("answers.$i") }}">

                                            @if ($errors->has("answers.$i"))
                                                <span class="help-block">
                                                    <strong>{{ $errors->first("answers.$i") }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endfor
                            </div>

                        @endif

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Submit
                                </button>
                            </div>
                        </div>
                    </form>
                    <hr/>
                    <form id="delete-user" class="form-horizontal" role="form" method="POST" action="{{ route('deleteUser', ['user' => $user]) }}">
                        {{ csrf_field() }}

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-4">
                                <button type="submit" class="btn btn-danger">
                                    Delete Account
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_after')
    <script>
        $('#delete-user').on('submit', function(ev){
            ev.preventDefault();
            alertify.confirm('Delete Account', 'Are you sure you want to delete your account? This action cannot be undone.', function(){
                ev.target.submit();
            }, function() {});
        });
    </script>
@endpush
