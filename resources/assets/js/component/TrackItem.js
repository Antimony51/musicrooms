import React from 'react';
import ScrollyText from './ScrollyText';

function TrackItem (props) {
    var track = props.track;

    if (track.type == 'file' || track.type == 'soundcloud'){
        var name = `${track.title || 'Unknown Title'} - ${track.artist || 'Unknown Artist'}`;
    }else if (track.type == 'youtube'){
        var name = track.title;
    }

    return (
        <div className="dyn-block-row">
            {
                (track.owner.name == app.currentUser.name ||
                    (app.currentRoom.owner && app.currentRoom.owner.name == app.currentUser.name) ||
                    app.currentUser.admin) ?
                (
                    <div className="icon-button track-remove rigid-right" onClick={props.onRequestRemove}><i className="fa fa-trash"></i></div>
                ) : null
            }
            <div className="track-duration rigid-right">{durationString(track.duration)}</div>
            <ScrollyText className="track-name fluid">{name}</ScrollyText>
        </div>
    );
}

export default TrackItem;
