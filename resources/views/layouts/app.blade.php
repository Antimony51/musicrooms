<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@lang('titles.app_name')</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: 'Lato';
        }
    </style>

    <script>
        app = {
            currentUser:
            @if (Auth::check())
                {!! json_encode(Auth::user()) !!}
            @else
                null
            @endif,
            csrf_token: '{{ csrf_token() }}',
            soundcloud_client_id: '{{ config('services.soundcloud.client_id') }}',
            youtube_api_key: '{{ config('services.youtube.key') }}',
        }
    </script>

    @stack('scripts_before')

    <script src="{{ asset('js/app.js') }}"></script>
</head>
<body id="app-layout">
    <div id="app-content">
        <nav class="navbar navbar-default navbar-static-top">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        @lang('titles.app_name')
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        <li><a href="{{ url('/home') }}">Home</a></li>
                        <li><a href="{{ url('/rooms') }}">Rooms</a></li>
                        @if (Auth::user() && Auth::user()->admin)
                            <li><a href="{{ url('/admin/users') }}">Users</a></li>
                        @endif
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ url('/login') }}">Login</a></li>
                            <li><a href="{{ url('/register') }}">Register</a></li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->displayName() }}
                                    @if (Auth::user()->admin)
                                        <span class="color-red user-role" title="Admin">[A]</span>
                                    @endif
                                    <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('user', ['user' => Auth::user()]) }}"><i class="fa fa-user"></i> Profile</a></li>
                                    <li><a href="{{ route('userSettings', ['user' => Auth::user()]) }}"><i class="fa fa-cog"></i> Settings</a></li>
                                    <li><a href="{{ url('/logout') }}"><i class="fa fa-sign-out"></i> Logout</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        <div class="container">
            <div class="row">
                <div id="content" class="col-sm-12 col-sm-offset-0">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @stack('scripts_after')
</body>
</html>
