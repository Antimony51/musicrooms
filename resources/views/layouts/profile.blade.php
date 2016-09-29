@extends('layouts/app')
@section('content')
    <script>
        app.currentProfile = {
            name: "{{ $user->name }}",
            cosmeticName: "{{ $user->profile->cosmetic_name }}",
            displayName: "{{$user->displayName()}}"
        }
    </script>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="media">
                <div class="media-left">
                    <img src="{{ $profile->iconLarge() }}" alt="Profile Picture"
                         style="width: 200px; height: 200px;">
                </div>
                <div class="media-body">
                    <h2 class="media-heading">{{ $user->displayName() }}</h2>
                    @if(!$ownProfile && Auth::check())
                        @include('widgets.manage-friend', ['compact' => false])
                    @endif
                    <h3>Plays: {{ $profile->plays }}</h3>
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