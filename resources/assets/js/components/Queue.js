var React = require('react');

module.exports = class Queue extends React.Component {
    render() {
        return (
            <ul className="list-group">
                {
                    this.props.tracks.map((track) => track && (
                        <li className="list-group-item" key={track.id}>
                            <Track track={track} />
                        </li>
                    ))
                }
            </ul>
        );
    }
}
