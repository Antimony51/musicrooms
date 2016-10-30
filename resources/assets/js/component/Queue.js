var React = require('react');
var Track = require('./TrackItem');

module.exports = function Queue(props) {
    return (
        <ul className="list-group">
            {
                props.tracks.map((track) => track && (
                    <li className="list-group-item" key={track.id}>
                        <Track track={track} />
                    </li>
                ))
            }
        </ul>
    );
}
