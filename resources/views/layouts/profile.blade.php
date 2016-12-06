@extends('layouts/app')

{{--
VARS:
$user, $profile, $ownProfile, $activeTab
--}}

@push('scripts_before')
    <script>
        app.currentProfile = {
            name: "{{ $user->name }}",
            cosmeticName: "{{ $profile->cosmetic_name }}",
            displayName: "{{$user->displayName()}}"
        }
    </script>
@endpush

@section('content')
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="profile-right-buttons text-right">
                @if(!$ownProfile && Auth::user()->admin)
                    <a href="{{ route('userSettings', ['user' => $user]) }}" class="btn btn-default">Settings</a>
                @endif
                @if($ownProfile || Auth::user()->admin)
                    <a href="{{ route('editProfile', ['user' => $user]) }}" class="btn btn-default">Edit Profile</a>
                @endif
            </div>
            <div class="media" style="margin-top: 0">
                <div class="media-left">
                    <img src="{{ $profile->iconLarge() }}" alt="Profile Picture"
                         style="width: 200px; height: 200px;">
                </div>
                <div class="media-body">
                    <h2 class="media-heading">
                        {{ $user->displayName() }}
                        @if ($user->admin)
                            <span class="color-red user-role" title="Admin">[A]</span>
                        @endif
                    </h2>
                    @if(!$ownProfile)
                        <p>
                            @include('widgets.manage-friend', ['compact' => false])
                        </p>
                    @endif
                    <h3>Plays: {{ $profile->plays }}</h3>
                    <h3>Favs: {{ $user->favoriteTracks()->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <nav>
        <ul class="nav nav-tabs">
            <li class="{{ ($activeTab == 'overview') ? 'active' : '' }}"><a href="{{ route('profileOverview', ['name' => $user->name]) }}">Overview</a></li>
            <li class="{{ ($activeTab == 'favorites') ? 'active' : '' }}"><a href="{{ route('profileFavorites', ['name' => $user->name]) }}">Favorites</a></li>
            <li class="{{ ($activeTab == 'friends') ? 'active' : '' }}"><a href="{{ route('profileFriends', ['name' => $user->name]) }}">Friends</a></li>
            <li class="{{ ($activeTab == 'rooms') ? 'active' : '' }}"><a href="{{ route('profileRooms', ['name' => $user->name]) }}">Rooms</a></li>
        </ul>
    </nav>
    <div class="tab-content panel panel-default tabbed-panel">
        <div class="panel-body">
            @yield('tabcontent')
        </div>
    </div>
@endsection()
