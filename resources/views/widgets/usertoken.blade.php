<div class="panel panel-default">
    <div class="panel-body">
        <div class="media">
            <div class="media-left">
                <a href="{{ route('user', ['user' => $user]) }}">
                    <img src="{{ $user->profile->iconSmall() }}" alt="Profile Picture"
                     style="width: 48px; height: 48px;">
                </a>
            </div>
            <div class="media-body">
                <a href="{{ route('user', ['user' => $user]) }}" class="media-heading">{{ $user->displayName() }}</a>
                @if(Auth::check() && !$user->is(Auth::user()))
                    <br>
                    @include('widgets.manage-friend', ['compact' => true])
                @endif
            </div>
        </div>
    </div>
</div>