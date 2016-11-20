import React from 'react';
import SeekBar from './SeekBar';

class Volume extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            volume: props.volume || 1,
            mute: props.mute || false,
            mouseOver: false,
            mouseDown: false
        };
    }

    handleMuteClick = () => {
        this.setState((prevState, props) => ({
            mute: !prevState.mute
        }), () => {
            if (_.isFunction(this.props.onChange)){
                this.props.onChange(this.state.volume, this.state.mute);
            }
        });
    }

    componentWillUnmount() {
        document.documentElement.removeEventListener('mouseup', this.handleMouseUp);
        document.documentElement.removeEventListener('dragend', this.handleMouseUp);
    }

    handleMouseEnter = () => {
        this.setState({mouseOver: true});
    }

    handleMouseLeave = () => {
        this.setState({mouseOver: false});
    }

    handleMouseDown = () => {
        this.setState({mouseDown: true});
        document.documentElement.addEventListener('mouseup', this.handleMouseUp);
        document.documentElement.addEventListener('dragend', this.handleMouseUp);
    }

    handleMouseUp = () => {
        this.setState({mouseDown: false});
    }

    handleSeekBarChange = (value) => {
        this.setState({volume: value});
        if (_.isFunction(this.props.onChange)){
            this.props.onChange(value, this.state.mute);
        }
    }

    render() {
        const {
            volume, mute, mouseDown, mouseOver
        } = this.state;

        const expanded = mouseDown || mouseOver;

        return (
            <span onMouseEnter={this.handleMouseEnter} onMouseLeave={this.handleMouseLeave}
                style={{position: 'relative'}} title="Volume">
                <span onClick={this.handleMuteClick}
                    className="icon-button"
                    style={{
                        display:'inline-block',
                        width: '1em',
                        textAlign: 'left'
                    }}
                >
                    {
                        mute ? (
                            <i className="fa fa-volume-mute" />
                        ) : (
                            volume <= 0.1 ? (
                                <i className="fa fa-volume-off" />
                            ) : volume <= 0.5 ? (
                                <i className="fa fa-volume-down" />
                            ) : (
                                <i className="fa fa-volume-up" />
                            )
                        )
                    }
                </span>
                <div onMouseDown={this.handleMouseDown}
                    style={{ height: expanded ? '68px' : '0',
                        padding: (expanded ? '4px' : '0') + ' 4px' ,
                        marginLeft: '-4px',
                        transition: 'height 0.2s, padding 0.2s',
                        position: 'absolute',
                        bottom: '1em',
                        overflow: 'hidden',
                        left: 0,
                    }}>
                    <SeekBar value={mute ? 0 : volume}
                        locked={mute || !expanded}
                        immediate="true"
                        vertical="true"
                        style={{ height: '60px' }}
                        onChange={this.handleSeekBarChange} />
                </div>
            </span>
        );
    }

}

export default Volume;
