@extends('layouts.profile')

@section('tabcontent')
    @if($favorites->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
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
                        <td>{{ $track->title }}</td>
                        <td>{{ $track->artist }}</td>
                        <td>{{ $track->album }}</td>
                        @if(Auth::check())
                            <td>
                                @if($mutualFavorites->contains($track))
                                    <span class="favorite-heart checked" data-track-id="{{ $track->id }}"></span>
                                @else
                                    <span class="favorite-heart" data-track-id="{{ $track->id }}"></span>
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