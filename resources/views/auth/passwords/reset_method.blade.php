@extends('layouts.app')

<!-- Main Content -->
@section('content')
<div class="container">
    <div class="row">
        <div class="col-sm-8 col-sm-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Reset Password</div>
                <div class="panel-body text-center">
                    <h4>Choose a method to reset your password:</h4>
                    <a href="{{ url('/password/email') }}" class="btn btn-primary">
                        <i class="fa fa-btn fa-envelope"></i> Email Me A Reset Link
                    </a>
                    <a href="{{ url('/password/questions') }}" class="btn btn-primary">
                        <i class="fa fa-btn fa-question"></i> Answer Security Questions
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
