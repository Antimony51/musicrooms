<span class="manage-friend" data-user="{{ $user->name }}" data-display-name="{{ $user->displayName() }}">
    @php
        $show_removefriend = false;
        $show_requestsent = false;
        $show_acceptrequest = false;
        $show_addfriend = false;
    @endphp
    @if($user->isFriendWith(Auth::user()))
        @php($show_removefriend = true)
    @elseif($user->hasFriendRequestFrom(Auth::user()))
        @php($show_requestsent = true)
    @elseif($user->hasSentFriendRequestTo(Auth::user()))
        @php($show_acceptrequest = true)
    @else
        @php($show_addfriend = true)
    @endif
    <button type="button" class="btn {{ $compact ? "btn-xs" : ""  }} btn-danger removefriend {{ $show_removefriend ? '' : 'hidden' }}">
        {{ $compact ? "Remove" : "Remove Friend"}}
    </button>
    <button type="button" class="btn {{ $compact ? "btn-xs" : ""  }} btn-info requestsent {{ $show_requestsent ? '' : 'hidden' }}">
        Request Sent
    </button>
    <button type="button" class="btn {{ $compact ? "btn-xs" : ""  }} btn-success acceptfriend {{ $show_acceptrequest ? '' : 'hidden' }}">
        {{ $compact ? "Accept" : "Accept Request"}}
    </button>
    <button type="button" class="btn {{ $compact ? "btn-xs" : ""  }} btn-danger declinefriend {{ $show_acceptrequest ? '' : 'hidden' }}">
        {{ $compact ? "Decline" : "Decline Request"}}
    </button>
    <button type="button" class="btn {{ $compact ? "btn-xs" : ""  }} btn-success addfriend {{ $show_addfriend ? '' : 'hidden' }}">
        {{ $compact ? "Add" : "Add Friend"}}
    </button>
    <span class="spinner {{ $compact ? "spinner-small" : "spinner-large"  }} hidden"></span>
</span>