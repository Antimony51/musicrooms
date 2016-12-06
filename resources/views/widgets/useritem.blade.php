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
                <span class="media-heading">
                    <a href="{{ route('user', ['user' => $user]) }}">
                        {{ $user->displayName() }}
                    </a>
                    @if ($user->admin)
                        <span class="color-red user-role" title="Admin">[A]</span>
                    @endif
                </span>
                @if(isset($settingsButton) && $settingsButton)
                    <br>
                    <a href="{{ route('userSettings', ['user' => $user]) }}" class="btn btn-xs btn-default">Settings</a>
                @else
                    @if(Auth::check() && !$user->is(Auth::user()))
                        <br>
                        @include('widgets.manage-friend', ['compact' => true])
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
