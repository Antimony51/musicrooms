import React from 'react';
import TrackItem from './TrackItem';

function Queue(props) {
    return (
        <ul className="list-group">
            {
                props.tracks.length == 0 ? (
                    <li className="list-group-item text-muted">The queue is empty.</li>
                ) : (
                    props.tracks.map((track, index) => track && (
                        <li className="list-group-item" style={{padding: 0}} key={track.key}>
                            <TrackItem track={track} onRequestRemove={() => props.onRequestRemove(track)} />
                        </li>
                    ))
                )
            }
        </ul>
    );
}

export default Queue;
