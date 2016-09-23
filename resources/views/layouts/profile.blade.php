@extends('layouts/app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="media">
                            <div class="media-left">
                                <img src="{{ $profile->iconLarge() }}" alt="Profile Picture"
                                     style="width: 200px; height: 200px;">
                            </div>
                            <div class="media-body">
                                <h2 class="media-heading">{{ $user->displayName() }}</h2>
                                <p><h3>Plays: {{ $profile->plays }}</h3></p>
                            </div>
                        </div>
                    </div>
                </div>

                        <nav>
                            <ul class="nav nav-tabs">
                                <li class="{{ ($activeTab == 'overview') ? 'active' : '' }}"><a href="overview">Overview</a></li>
                                <li class="{{ ($activeTab == 'favs') ? 'active' : '' }}"><a href="favs">Favorites</a></li>
                                <li class="{{ ($activeTab == 'friends') ? 'active' : '' }}"><a href="friends">Friends</a></li>
                            </ul>
                        </nav>
                        <div class="tab-content panel panel-default tabbed-panel">
                            <div class="panel-body">
                                @yield('content')
                                asdf
                            </div>
                        </div>

            </div>
        </div>
    </div>
@endsection()