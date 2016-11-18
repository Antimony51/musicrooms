import React from 'react';

class FavoriteHeart extends React.Component {

    constructor(props) {
        super(props);
        if (props.track){
            this.state = {
                trackId: props.track.id,
                checked: props.track.isFaved,
                wait: false
            };
        }else{
            this.state = {
                trackId: props.trackId,
                checked: props.checked,
                wait: false
            };
        }

    }

    handleClick = () => {
        const {
            trackId, checked, wait
        } = this.state;

        if (!wait){
            this.setState({
                wait: true
            });
            if (checked){
                $.ajax({
                    url: '/user/' + app.currentUser.name + '/favorites/remove/' + trackId,
                    method: 'POST'
                })
                    .done(() => {
                        this.setState({
                            checked: false
                        });
                    })
                    .fail(() => {
                        alertify.error('Removing favorite failed.');
                    })
                    .always(() => {
                        this.setState({
                            wait: false
                        });
                    });
            }else{
                $.ajax({
                    url: '/user/' + app.currentUser.name + '/favorites/add/' + trackId,
                    method: 'POST',
                })
                    .done(() => {
                        this.setState({
                            checked: true
                        });
                    })
                    .fail(() => {
                        alertify.error('Adding favorite failed.');
                    })
                    .always(() => {
                        this.setState({
                            wait: false
                        });
                    });
            }
        }
    }

    render() {

        const {
            checked, wait
        } = this.state;

        var className = 'favorite-heart';
        if (checked){
            className += ' checked';
        }
        if (wait){
            className += ' spinner';
        }
        if (this.props.className){
            className += ' ' + this.props.className;
        }

        return (
            <span className={className} style={this.props.style} onClick={this.handleClick} />
        );
    }

}

export default FavoriteHeart;
