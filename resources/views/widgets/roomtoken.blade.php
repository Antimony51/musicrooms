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
            {{ $room->currentTrack->title }} - {{ $room->currentTrack->artist }}
        @else
            No track currently playing.
        @endif
    </div>
</div>