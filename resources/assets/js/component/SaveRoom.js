import React from 'react';

class SaveRoom extends React.Component {

    constructor(props) {
        super(props);
        this.state = {

        };
    }

    render() {

        var compact = (this.props.compact > 0 || this.props.compact === 'true') ? true : false;
        var className = compact ? 'btn-xs' : '';

        const {
            room
        } = this.props;
        return (
            room.isSaved !== null && (
                room.isSaved ? (
                    <button className={'btn ' + className}>
                        Saved
                    </button>
                ) : (
                    <button className={'btn btn-primary' + className}>
                        Save
                    </button>
                )
            )
        );
    }

}

export default SaveRoom;
