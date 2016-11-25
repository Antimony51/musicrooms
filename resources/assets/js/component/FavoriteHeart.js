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

    componentWillReceiveProps(nextProps) {
        if (nextProps.track){
            if (nextProps.track.id != this.state.trackId){
                this.setState({
                    trackId: nextProps.track.id,
                    checked: nextProps.track.isFaved,
                    wait: false
                });
            }
        }else{
            if (nextProps.trackId != this.state.trackId){
                this.setState({
                    trackId: nextProps.trackId,
                    checked: nextProps.checked,
                    wait: false
                });
            }
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
                    url: '/favorites/remove/' + trackId,
                    method: 'POST'
                })
                    .done(() => {
                        if (this.props.onChange){
                            this.props.onChange(false);
                        }
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
                    url: '/favorites/add/' + trackId,
                    method: 'POST',
                })
                    .done(() => {
                        if (this.props.onChange){
                            this.props.onChange(true);
                        }
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

        return (
            <span className={this.props.className} style={this.props.style}
            title={checked ? 'Remove Favorite' : 'Add Favorite'}>
                <span className={className} onClick={this.handleClick} />
            </span>
        );
    }

}

export default FavoriteHeart;
