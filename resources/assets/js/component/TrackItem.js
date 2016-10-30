var React = require('react');

module.exports = function TrackItem (props) {
    var track = props.track;

    if (track.type == 'file' || track.type == 'soundcloud'){
        var name = `${track.title || 'Unknown Title'} - ${track.artist || 'Unknown Artist'}`;
    }else if (track.type == 'youtube'){
        var name = track.title;
    }

    return (
        <div>
            <span className="track-name">{name}</span>
            <span className="track-duration">{durationString(track.duration)}</span>
        </div>
    );
};