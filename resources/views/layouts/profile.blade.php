@extends('layouts/app')
@section('content')
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
            <li class="{{ ($activeTab == 'overview') ? 'active' : '' }}"><a href="{{ route('profileOverview', ['name' => $user->name]) }}">Overview</a></li>
            <li class="{{ ($activeTab == 'favorites') ? 'active' : '' }}"><a href="{{ route('profileFavorites', ['name' => $user->name]) }}">Favorites</a></li>
            <li class="{{ ($activeTab == 'friends') ? 'active' : '' }}"><a href="{{ route('profileFriends', ['name' => $user->name]) }}">Friends</a></li>
        </ul>
    </nav>
    <div class="tab-content panel panel-default tabbed-panel">
        <div class="panel-body">
            @yield('tabcontent')
        </div>
    </div>
@endsection()