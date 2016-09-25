<a href="{{ route('profile', ['name' => $user->name]) }}">
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="media">
                <div class="media-left">
                    <img src="{{ $user->profile->iconSmall() }}" alt="Profile Picture"
                         style="width: 48px; height: 48px;">
                </div>
                <div class="media-body">
                    <span class="media-heading">{{ $user->displayName() }}</span>
                </div>
            </div>
        </div>
    </div>
</a>