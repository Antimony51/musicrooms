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
                        @if($track->type === 'youtube')
                            <td><i class="fa fa-youtube color-youtube"/></td>
                        @elseif ($track->type === 'soundcloud')
                            <td><i class="fa fa-soundcloud color-soundcloud"/></td>
                        @elseif ($track->type === 'file')
                            <td><i class="fa fa-file-audio-o"/></td>
                        @else
                            <td></td>
                        @endif
                        <td>{{ $track->title }}</td>
                        <td>{{ $track->artist }}</td>
                        <td>{{ $track->album }}</td>
                        @if(Auth::check())
                            <td>
                                @if($mutualFavorites->contains($track))
                                    <span class="favorite-heart-root" data-checked="true" data-track-id="{{ $track->id }}"></span>
                                @else
                                    <span class="favorite-heart-root" data-track-id="{{ $track->id }}"></span>
                                @endif
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
