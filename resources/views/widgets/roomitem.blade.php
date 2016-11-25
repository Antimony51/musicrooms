<div class="panel panel-default">
    <div class="panel-body">
        @php
            $currentTrack = $room->currentTrack();
            $userCount = $room->userCount();
        @endphp
        <a href="{{ route('room', ['room' => $room]) }}">
            {{ $room->title }}
            @if ($room->visibility == 'private')
                <strong class="text-danger">(Private)</strong>
            @endif
        </a>
        <span class="pull-right text-muted">
            Users: {{ $userCount }}
        </span>
        <br>
        @if(!is_null($currentTrack))
            Now Playing:
            @if($currentTrack->type == 'file' || $currentTrack->type == 'soundcloud')
                {{ $currentTrack->title ?: 'Unknown Title' . ' - ' . $currentTrack->artist ?: 'Unknown Artist' }}
            @elseif($currentTrack->type == 'youtube')
                {{ $currentTrack->title ?: 'Unknown Title' }}
            @endif
        @else
            No track currently playing.
        @endif
    </div>
</div>
