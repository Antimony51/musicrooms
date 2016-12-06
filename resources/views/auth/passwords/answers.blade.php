@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Reset Password</div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('/password/answers') }}">
                        {{ csrf_field() }}

                        <input type="hidden" name="email" value="{{ $user->email }}">

                        <div class="{{ $errors->has("answers") ? ' has-error' : '' }}">
                            <div class="form-group">
                                @if ($errors->has("answers"))
                                    <div class="help-block col-sm-6 col-sm-offset-4">
                                        <strong>You must provide {{ config('auth.passwords.users.num_security_questions') }} answers.</strong>
                                    </div>
                                @endif
                            </div>

                            @for ($i=0; $i < config('auth.passwords.users.num_security_questions'); $i++)
                                @if($i != 0)
                                    <br>
                                @endif

                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Question {{$i+1}}</label>

                                    <div class="col-sm-6">
                                        {{ $questions[$i] }}
                                    </div>
                                </div>
                                <div class="form-group{{ $errors->has("answers.$i") ? ' has-error' : '' }}">
                                    <label for="answer{{$i}}" class="col-sm-4 control-label">Answer {{$i+1}}</label>

                                    <div class="col-sm-6">
                                        <input id="answer{{$i}}" type="text" maxlength="255" class="form-control" autocomplete="off"
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

                        <div class="form-group">
                            <div class="col-sm-6 col-sm-offset-4">
                                <button type="submit" class="btn btn-primary">
                                    Submit
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
