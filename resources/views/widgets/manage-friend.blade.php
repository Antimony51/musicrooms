<span class="manage-friend-root"
    data-name="{{ $user->name }}"
    data-display-name="{{ $user->displayName() }}"
    data-friend-status="{{ $user->friendStatus() }}"
    {!!
        $compact ? 'data-compact="true"' : ''
    !!}
></span>
