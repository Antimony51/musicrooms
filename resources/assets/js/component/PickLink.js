import React from 'react';
import Track from '../class/Track';

class PickLink extends React.Component {

    timeout = null;

    constructor(props) {
        super(props);

        this.state = {
            track: null,
            error: null,
            wait: false
        };
    }

    newValue (value) {
        var track = null;
        var uri = new URI(value);
        if (uri.domain() == 'youtube.com'){
            var query = URI.parseQuery(uri.query());
            if (uri.path() == '/watch' && query.v){
                track = new Track({
                    type: 'youtube',
                    uri: query.v
                });
            }
        }else if (uri.domain() == 'youtu.be'){
            if (uri.segment().length == 1){
                track = new Track({
                    type: 'youtube',
                    uri: uri.filename()
                });
            }
        }else if (uri.domain() == 'soundcloud.com'){
            if (uri.segment().length == 2){
                track = new Track({
                    type: 'soundcloud',
                    uri: uri.path()
                });
            }
        }

        if(track && track.type && track.uri){
            switch (track.type){
                case 'youtube':
                    $.ajax({
                        url: 'https://www.googleapis.com/youtube/v3/videos',
                        method: 'get',
                        data: {
                            part: 'status,snippet,contentDetails',
                            id: track.uri,
                            key: app.youtube_api_key
                        }
                    })
                        .done((data) => {
                            if (data.items.length == 0){
                                this.setState({
                                    track: null,
                                    error: 'This YouTube video does not exist'
                                });
                            }else{
                                var status = data.items[0].status;
                                var snippet = data.items[0].snippet;
                                var contentDetails = data.items[0].contentDetails;
                                if (snippet.liveBroadcastContent != 'none'){
                                    this.setState({
                                        track: null,
                                        error: "Live streams can't be added"
                                    });
                                }else if (!status.embeddable){
                                    this.setState({
                                        track: null,
                                        error: 'Embedding is disabled for this video'
                                    });
                                }else{
                                    track.title = snippet.title;
                                    track.duration = moment.duration(contentDetails.duration).asSeconds();
                                    track.link = 'http://youtube.com/watch?v=' + track.uri;

                                    this.setState({
                                        track: track
                                    });
                                }
                            }
                        })
                        .fail(() => {
                            this.setState({
                                track: null,
                                error: 'Something went wrong'
                            });
                        })
                        .always(() => {
                            this.setState({
                                wait: false
                            });
                        });
                    break;
                case 'soundcloud':
                    $.ajax({
                        url: 'http://api.soundcloud.com/resolve',
                        method: 'get',
                        data: {
                            url: 'http://soundcloud.com' + track.uri,
                            client_id: app.soundcloud_client_id
                        }
                    })
                        .done((data) => {
                            if (!data.streamable || data.embeddable_by != 'all'){
                                this.setState({
                                    track: null,
                                    error: 'Embedding is disabled for this track'
                                });
                            }else{
                                track.uri = data.id;
                                track.title = data.title;
                                track.artist = data.user.username;
                                track.duration = data.duration / 1000;
                                track.link = data.permalink_url;

                                this.setState({
                                    track: track
                                });
                            }
                        })
                        .fail((jqXHR) => {
                            if (jqXHR.status == 404){
                                this.setState({
                                    track: null,
                                    error: 'Track not found'
                                });
                            }else if (jqXHR.status == 403){
                                this.setState({
                                    track: null,
                                    error: 'Embedding is disabled for this track'
                                });
                            }else{
                                this.setState({
                                    track: null,
                                    error: 'Something went wrong'
                                });
                            }
                        })
                        .always(() => {
                            this.setState({
                                wait: false
                            });
                        });
                    break;
            }
        }else{
            this.setState({
                track: null,
                wait: false,
                error: 'Not a valid YouTube or Soundcloud URL'
            });
        }
    }

    handleOnChange = (ev) => {
        this.setState({
            error: null
        });

        clearTimeout(this.timeout);
        this.timeout = null;

        var value = _.trim(ev.target.value);

        if (value.length == 0){
            this.setState({
                track: null,
                wait: false
            });
            return;
        }

        this.timeout = setTimeout(() => this.newValue(value), 200);

        this.setState({
            wait: true
        });
    };

    handleSubmit = (ev) => {
        ev.preventDefault();

        if (!this.state.wait){
            if (this.state.track && this.state.track.type && this.state.track.uri){
                this.props.onSelect(this.state.track);
            }else{
                if (!this.state.error){
                    this.setState({
                        error: 'Not a valid YouTube or Soundcloud URL'
                    });
                }
            }
        }
    };

    render() {
        var type = this.state.track ? this.state.track.type : null;
        var error = this.state.error;
        var wait = this.state.wait;

        return (
            <form className="form-horizontal" onSubmit={this.handleSubmit}>
                <div className={ 'form-group' + (error ? ' has-error' : '') }>
                    <div className="col-md-12">
                        <div className="input-group">
                            <span className="input-group-addon">
                                <i className={
                                    'fa ' + (
                                        wait ? 'fa-spinner fa-spin' :
                                        type == 'youtube' ? 'fa-youtube color-youtube' :
                                        type == 'soundcloud' ? 'fa-soundcloud color-soundcloud' :
                                        'fa-link'
                                    )
                                }></i>
                            </span>
                            <input type="text" className="form-control" name="url"
                                onChange={this.handleOnChange} autoComplete="off"
                                ref={(el) => this.input = el}/>
                        </div>
                        {
                            error ? (
                                <span className="help-block">
                                    <strong>{error}</strong>
                                </span>
                            ) : null
                        }
                    </div>
                </div>
            </form>
        );
    }
}

export default PickLink;
