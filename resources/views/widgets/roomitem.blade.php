<div class="panel panel-default">
    <div class="panel-body">
        <a href="{{ route('room', ['room' => $room]) }}">
            {{ $room->title }}
            @if ($room->visibility == 'private')
                <strong class="color-red">(Private)</strong>
            @endif
        </a>
        <br>
        @if(!is_null($room->currentTrack))
            Now Playing:
            @if($room->currentTrack->type == 'file' || $room->currentTrack->type == 'soundcloud')
                {{ $room->currentTrack->title ?: 'Unknown Title' . ' - ' . $room->currentTrack->artist ?: 'Unknown Artist' }}
            @elseif($room->currentTrack->type == 'youtube')
                {{ $room->currentTrack->title ?: 'Unknown Title' }}
            @endif
        @else
            No track currently playing.
        @endif
    </div>
</div>
