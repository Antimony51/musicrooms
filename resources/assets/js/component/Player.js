import React from 'react';
import ReactPlayer from 'react-player';
import SeekBar from './SeekBar';
import ScrollyText from './ScrollyText';

const youtubeConfig = {};
const soundcloudConfig = {
    clientId: app.soundcloud_client_id
};
const fileConfig = {};

class Player extends React.Component {

    player = null;
    ready = false;

    constructor(props) {
        super(props);
        this.state = {
            track: props.track,
            playing: props.playing || true,
            played: props.track ? props.seek/props.track.duration : 0,
            volume: props.volume || 1,
        };
    }

    handleReady = () => {
        if (!this.ready){
            this.ready = true;
            if (this.props.seek){
                this.seek(this.props.seek);
            }
        }
    };

    handleBuffer = () => {
        this.ready = false;
    };

    handleProgress = (state) => {
        this.setState({
            played: state.played
        });
    };

    seek = (seek) => {
        var track = this.state.track;
        if (track){
            this.seekFraction(seek/track.duration);
        }
    }

    seekFraction = (seek) => {
        if (this.player){
            //console.log('seek', this.state.played*this.state.track.duration + ' -> ' + seek*this.state.track.duration);
            this.player.seekTo(seek);
        }
    };

    handleSeekBarChange = (value) => {
        this.seekFraction(value);
    };

    componentWillReceiveProps(nextProps) {
        this.setState({
            track: nextProps.track,
            playing: nextProps.playing || true,
            volume: nextProps.volume || 1,
        });
        if (nextProps.seek && this.state.track){
            var playedTime = this.state.played * this.state.track.duration;
            if(!_.inRange(nextProps.seek, playedTime-5, playedTime+5)){
                this.seek(nextProps.seek);
            }
        }
    }

    render() {

        const {
            track, playing, played, volume
        } = this.state;
        return (
            <div>
                <div className="container-fluid">
                    <div className="row">
                        <div className="col-sm-12">
                            <ScrollyText className="h3 text-center">
                                {
                                    track ? (track.title || 'Unknown Title') : 'Nothing Playing'
                                }
                            </ScrollyText>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-sm-3 hidden-xs">
                            <div className="h5">
                                {
                                    track ? (
                                        durationString(played * track.duration) + ' / ' + durationString(track.duration)
                                    ) : (
                                        durationString(0) + ' / ' + durationString(0)
                                    )
                                }
                            </div>
                        </div>
                        <div className="col-sm-6">
                            <ScrollyText className="h5 text-center">
                                {
                                    (track && (track.type == 'file' || track.type == 'soundcloud')) && (
                                        track.artist || 'Unknown Artist'
                                    )
                                }
                            </ScrollyText>
                        </div>
                        <div className="col-sm-3">

                        </div>
                    </div>
                </div>
                <div>
                    <SeekBar value={track ? played : null} onChange={this.handleSeekBarChange} locked="true" />
                    {
                        track && <ReactPlayer
                            ref={(player) => this.player = player}
                            url={track.link}
                            playing={playing}
                            volume={volume}
                            youtubeConfig={youtubeConfig}
                            soundcloudConfig={soundcloudConfig}
                            fileConfig={fileConfig}
                            onProgress={this.handleProgress}
                            onReady={this.handleReady}
                            onBuffer={this.handleBuffer}
                            hidden
                            />
                    }
                </div>
                <br/>
            </div>
        );
    }

}

export default Player;
