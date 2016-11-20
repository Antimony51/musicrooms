@extends('layouts.profile')

@section('tabcontent')
    @if($favorites->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th></th>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Album</th>
                    @if(Auth::check())
                        <th></th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($favorites as $track)
                    <tr>
                        <td class="text-center">
                            @if($track->type === 'youtube')
                                <i class="fa fa-youtube color-youtube"></i>
                            @elseif ($track->type === 'soundcloud')
                                <i class="fa fa-soundcloud color-soundcloud"></i>
                            @elseif ($track->type === 'file')
                                <i class="fa fa-file-audio-o"></i>
                            @endif
                        </td>
                        <td>{{ $track->title }}</td>
                        <td>{{ $track->artist }}</td>
                        <td>{{ $track->album }}</td>
                        @if(Auth::check())
                            <td class="text-right">
                                @if($track->type === 'youtube' || $track->type === 'soundcloud')
                                    <span class="spacer-after">
                                        <span class="copy-link-root" data-link="{{ $track->link }}"></span>
                                    </span>
                                @endif
                                <span class="favorite-heart-root" data-track-id="{{ $track->id }}"
                                    @if($mutualFavorites->contains($track))
                                        data-checked="true"
                                    @endif
                                    >
                                </span>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        This user has no favorite tracks.
    @endif
@endsection
