import React from 'react';
import ReactPlayer from 'react-player';
import SeekBar from './SeekBar';
import ScrollyText from './ScrollyText';
import FavoriteHeart from './FavoriteHeart';
import CopyLink from './CopyLink';
import Volume from './Volume';

const youtubeConfig = {
    preload: true
};
const soundcloudConfig = {
    preload: true,
    clientId: app.soundcloud_client_id
};
const fileConfig = {
    preload: 'auto'
};

class Player extends React.Component {

    player = null;
    preloadPlayer = null;
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
            preloadTrack: null,
            playerKey: 0
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

    handleEnded = () => {
        if (this.props.onEnded){
            this.props.onEnded();
        }
        this.setState({
            played: 0
        });
    }

    handleProgress = (state) => {
        this.setState({
            played: state.played
        });
        if (this.props.onProgress){
            this.props.onProgress(state.played);
        }
    };

    seek = (seek) => {
        var track = this.state.track;
        if (track){
            this.seekFraction(seek/track.duration);
        }
    }

    seekFraction = (seek) => {
        if (this.player && _.isFinite(seek)){
            //console.log('seek', this.state.played*this.state.track.duration + ' -> ' + seek*this.state.track.duration);
            this.player.seekTo(seek);
        }else if (!_.isFinite(seek)){
            console.error('Invalid seek:', seek);
        }
    };

    handleSeekBarChange = (value) => {
        this.seekFraction(value);
    };

    componentWillReceiveProps(nextProps) {
        var playerKey = this.state.playerKey;
        if (this.state.preloadTrack && nextProps.track &&
            this.state.preloadTrack.key == nextProps.track.key)
        {
            playerKey = (playerKey + 1) % 2;
            this.player = this.preloadPlayer;
            this.preloadPlayer = null;
        }
        var seek = nextProps.seek;
        this.setState({
            track: nextProps.track,
            playing: nextProps.playing || true,
            preloadTrack: nextProps.preloadTrack,
            playerKey: playerKey
        }, () => {
            if (!_.isNil(seek) && this.state.track){
                var playedTime = this.state.played * this.state.track.duration;
                if(!_.inRange(seek, playedTime-5, playedTime+5)){
                    this.seek(seek);
                }
            }
        });

    }

    render() {

        const {
            track, playing, played, volume, mute, preloadTrack, playerKey
        } = this.state;

        var players = [];

        if (track){
            players.push(<ReactPlayer key={playerKey}
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
                onEnded={this.handleEnded}
                hidden
                />);

            if (preloadTrack){
                players.push(<ReactPlayer key={(playerKey+1) % 2}
                    ref={(player) => this.preloadPlayer = player}
                    url={preloadTrack.link}
                    playing={false}
                    volume={mute ? 0 : volume}
                    youtubeConfig={youtubeConfig}
                    soundcloudConfig={soundcloudConfig}
                    fileConfig={fileConfig}
                    hidden
                    />);
            }
        }

        return (
            <div>
                <div className="container-fluid">
                    <div className="row">
                        <div className="col-sm-12">
                            <ScrollyText className="h3 text-center">
                                {
                                    track ? (
                                        (track.title || 'Unknown Title') + (
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
                        <div className="col-xs-3">
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
                        <div className="col-xs-6 text-center">
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
                        <div className="col-xs-3 text-right">
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
                        players
                    }
                </div>
                <br/>
            </div>
        );
    }

}

export default Player;
