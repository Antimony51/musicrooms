import React from 'react';
import ReactPlayer from 'react-player';
import SeekBar from './SeekBar';
import ScrollyText from './ScrollyText';
import FavoriteHeart from './FavoriteHeart';
import CopyLink from './CopyLink';
import Volume from './Volume';

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

        var volumeState = JSON.parse(localStorage.getItem('volumeState')) || {};
        this.state = {
            track: props.track,
            playing: props.playing || true,
            played: props.track ? props.seek/props.track.duration : 0,
            volume:
                (_.isNumber(volumeState.volume) && !_.isNaN(volumeState.volume)) ?
                    volumeState.volume : 1,
            mute: _.isBoolean(volumeState.mute) ? volumeState.mute : false,
        };
    }

    saveVolumeState = (volumeState) => {
        localStorage.setItem('volumeState', JSON.stringify(volumeState));
    };

    handleVolumeChange = (volume, mute) => {
        var volumeState = {
            volume: volume,
            mute: mute
        };
        this.setState(volumeState);
        this.saveVolumeState(volumeState);
    };

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
            track, playing, played, volume, mute
        } = this.state;
        return (
            <div>
                <div className="container-fluid">
                    <div className="row">
                        <div className="col-sm-12">
                            <ScrollyText className="h3 text-center">
                                {
                                    track ? (
                                        track.title || 'Unknown Title' + (
                                            (track.type == 'file' || track.type == 'soundcloud') ? (
                                                ' - ' + (track.artist || 'Unknown Artist')
                                            ) : ''
                                        )
                                    ) : 'Nothing Playing'
                                }
                            </ScrollyText>
                        </div>
                    </div>
                    <div className="row h5">
                        <div className="col-sm-3 hidden-xs">
                            <div>
                                {
                                    track ? (
                                        durationString(played * track.duration) + ' / ' + durationString(track.duration)
                                    ) : (
                                        durationString(0) + ' / ' + durationString(0)
                                    )
                                }
                            </div>
                        </div>
                        <div className="col-sm-6 text-center">
                            {
                                track && (
                                    <span className="text-muted">
                                        Added By: {
                                            <a href={'/user/' + track.owner.name}>
                                                {track.owner.displayName}
                                            </a>
                                        }
                                    </span>
                                )
                            }
                        </div>
                        <div className="col-sm-3 text-center-xs text-right-sm">
                            {
                                track && (track.type === 'youtube' || track.type === 'soundcloud') && (
                                    <CopyLink link={track.link} className="spacer-after" />
                                )
                            }
                            {
                                track && !_.isNil(track.isFaved) && (
                                    <FavoriteHeart track={track} className="spacer-after" />
                                )
                            }
                            <Volume volume={volume} mute={mute} onChange={this.handleVolumeChange}/>
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
                            volume={mute ? 0 : volume}
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
