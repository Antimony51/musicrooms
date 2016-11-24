import React from 'react';
import UserList from './UserList';
import AddTrackButton from './AddTrackButton';
import Queue from './Queue';
import Player from './Player';
import SaveRoom from './SaveRoom';

class Room extends React.Component {

    syncTimer = null;
    syncFails = 0;
    doSync = true;
    receivedState = null;
    userData = {};
    trackData = {};

    constructor(props) {
        super(props);

        this.state = {
            loading: true,
            users: [],
            queue: [],
            currentTrack: null,
            preloadTrack: null,
            seek: 0,
            descriptionExpanded: false,
            descriptionCanExpand: app.currentRoom.description.indexOf('\n') !== -1,
            uploadProgress: null,
            transcoding: false,
            uploads: []
        };
    }

    handleBeforeUnload = () => {
        $.ajax({
            url: `/room/${app.currentRoom.name}/leave`,
            method: 'post',
            async: false,
        });
    }

    getData(request, callback){

        if (Object.keys(request).length > 0) {
            $.ajax({
                url: `/room/${app.currentRoom.name}/getdata`,
                method: 'get',
                dataType: 'json',
                data: request
            })
                .done(function (data) {
                    callback(data);
                });
        }
    }

    shouldComponentUpdate(nextProps, nextState){
        if ((this.state.currentTrack ? !nextState.currentTrack : nextState.currentTrack) ||
            (this.state.currentTrack && nextState.currentTrack &&
                this.state.currentTrack.key !== nextState.currentTrack.key) ||
            (this.state.preloadTrack ? !nextState.preloadTrack : nextState.preloadTrack) ||
                (this.state.preloadTrack && nextState.preloadTrack &&
                    this.state.preloadTrack.key !== nextState.preloadTrack.key) ||
            this.state.loading !== nextState.loading ||
            this.state.queue.length !== nextState.queue.length ||
            this.state.users.length !== nextState.users.length ||
            this.state.seek !== nextState.seek ||
            this.state.descriptionExpanded !== nextState.descriptionExpanded ||
            this.state.descriptionCanExpand !== nextState.descriptionCanExpand ||
            this.state.uploadProgress !== nextState.uploadProgress ||
            this.state.transcoding !== nextState.transcoding ||
            this.state.uploads.length !== nextState.uploads.length
        ){
            return true;
        }

        for (let i = 0, len = this.state.queue.length; i < len; i++){
            if ((this.state.queue[i] ? !nextState.queue[i] : nextState.queue[i]) ||
                this.state.queue[i] && nextState.queue[i] &&
                this.state.queue[i].key !== nextState.queue[i].key)
            {
                return true;
            }
        }

        for (let i = 0, len = this.state.users.length; i < len; i++){
            if (this.state.users[i] !== nextState.users[i]){
                return true;
            }
        }

        for (let i = 0, len = this.state.uploads.length; i < len; i++){
            if ((this.state.uploads[i] ? !nextState.uploads[i] : nextState.uploads[i]) ||
                this.state.uploads[i] && nextState.uploads[i] &&
                this.state.uploads[i].name !== nextState.uploads[i].name)
            {
                return true;
            }
        }

        return false;
    }

    updateState (){
        var currentTrack = null;
        var preloadTrack = this.state.preloadTrack;
        if (this.receivedState.currentTrack &&
            this.trackData[this.receivedState.currentTrack] &&
            this.userData[this.receivedState.currentTrackMeta.owner])
        {
            currentTrack = _.assign(
                {},
                this.trackData[this.receivedState.currentTrack],
                {
                    owner: this.userData[this.receivedState.currentTrackMeta.owner],
                    key: this.receivedState.currentTrackMeta.key
                }
            );
        }

        if ((this.state.currentTrack ? !currentTrack : currentTrack) ||
            (this.state.currentTrack && currentTrack &&
                this.state.currentTrack.key !== currentTrack.key))
        {
            preloadTrack = null;
        }

        this.setState({
            users: this.receivedState.users.map((userName) => (this.userData[userName] || userName)),
            queue: this.receivedState.queue.map((trackId, index) => {
                var track = null;
                if (this.trackData[trackId]){
                    track = _.assign(
                        {},
                        this.trackData[trackId],
                        {
                            owner: this.userData[this.receivedState.queueMeta[index].owner],
                            key: this.receivedState.queueMeta[index].key
                        }
                    );
                }
                return track;
            }),
            currentTrack: currentTrack,
            preloadTrack: preloadTrack,
            seek: this.receivedState.seek,
        });
    }

    processStateChange (newState){
        var newUsers, newTracks;
        if (!this.receivedState){
            newUsers = new Set(newState.users);
            newTracks = new Set(newState.queue);
            if (!_.isNil(newState.currentTrack)) {
                newTracks.add(newState.currentTrack)
            }
        }else{
            newUsers = new Set(newState.users.filter((username) => {
                return !this.userData.hasOwnProperty(username);
            }));

            newTracks = new Set(newState.queue.filter((trackid) => {
                return !this.trackData.hasOwnProperty(trackid);
            }));

            if (!_.isNil(newState.currentTrack) && !this.trackData.hasOwnProperty(newState.currentTrack)){
                newTracks.add(newState.currentTrack);
            }

            if (this.receivedState.roomDataToken != newState.roomDataToken){
                $.ajax({
                    url: `/room/${app.currentRoom.name}/data`,
                    method: 'get',
                    dataType: 'json',
                    maxTries: 3,
                    success: function (data) {
                        app.currentRoom = data;
                    },
                    error: function () {
                        if (--this.maxTries){
                            $.ajax(this);
                        }
                    }
                });
            }
        }

        this.receivedState = newState;

        this.updateState();

        if (newUsers.size || newTracks.size){
            this.getData({
                users: Array.from(newUsers),
                tracks: Array.from(newTracks)
            }, (data) => {
                if (data.users) {
                    for (let user of data.users) {
                        this.userData[user.name] = user;
                    }
                }
                if (data.tracks) {
                    for (let track of data.tracks) {
                        this.trackData[track.id] = track;
                    }
                }

                this.updateState();
            });
        }
    }

    sync = (repeat, callback) => {
        repeat = _.isNil(repeat) ? true : !!repeat;
        if (!this.doSync){
            return;
        }
        var startTime = Date.now();
        $.ajax({
            url: `/room/${app.currentRoom.name}/syncme`,
            method: 'get',
            dataType: 'json'
        })
            .done((data) => {
                this.syncFails = 0;
                this.processStateChange(data);
            })
            .fail(() => {
                this.syncFails++;
                if (this.syncFails == 3){
                    this.props.unmount();
                    alertify.alert('Error', 'Connection lost',
                        function(){
                            location = "/rooms";
                        });
                }
            })
            .always(() => {
                var now = Date.now();
                if (repeat && this.doSync){
                    setTimeout(this.sync, Math.max(2000 - (now - startTime),0));
                }
                if (callback){
                    callback();
                }
            });
    }

    componentDidMount(){
        $.ajax({
            url: `/room/${app.currentRoom.name}/join`,
            method: 'post',
            dataType: 'json',
        })
            .done((data) => {
                if (this.state.loading){
                    this.setState({
                        loading: false
                    });
                }
                this.processStateChange(data);
                window.addEventListener('beforeunload', this.handleBeforeUnload);
                this.syncTimer = setTimeout(this.sync, 1000);
            })
            .fail((xhr) => {
                this.props.unmount();
                alertify.alert('Error', 'Failed to join: ' + xhr.responseText,
                    function(){
                        location = "/rooms";
                    });
            });
        window.addEventListener('resize', this.checkDescriptionOverflow);
    }

    componentWillUnmount(){
        window.removeEventListener('beforeunload', this.handleBeforeUnload);
        window.removeEventListener('resize', this.checkDescriptionOverflow);
        clearTimeout(this.syncTimer);
        this.doSync = false;
    }

    handleRequestRemove = (track) => {
        $.ajax({
            url: `/room/${app.currentRoom.name}/removetrack`,
            method: 'post',
            data: {
                key: track.key
            }
        })
            .done(() => {
                this.setState({
                    queue: this.state.queue.filter((v) => v.key !== track.key )
                });
            })
            .fail(() => {
                alertify.error('Removing track failed');
            });
    };

    startUpload () {

        var data = new FormData();
        data.append('type', 'file');
        data.append('_token', app.csrf_token);
        data.append('file', this.state.uploads[0]);

        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', this.handleUploadProgress);
        xhr.addEventListener('error', this.handleUploadFailed);
        xhr.addEventListener('readystatechange', () => {
            if (xhr.readyState == 4){
                if (xhr.status >= 400){
                    this.handleUploadFailed();
                }else{
                    this.handleUploadSuccess();
                }
                this.handleUploadComplete();
            }
        });

        this.setState({
            uploadProgress: 0,
            transcoding: false
        });

        this.transcodingTimeout = null;

        xhr.open('POST', `/room/${app.currentRoom.name}/addtrack`);
        xhr.send(data);

    }

    transcodingTimeout = null;

    handleUploadProgress = (ev) => {
        this.setState({
            uploadProgress: ev.loaded / ev.total
        });

        if (!this.state.transcoding && _.isNull(this.transcodingTimeout) && ev.loaded == ev.total){
            this.transcodingTimeout = setTimeout(()=>{
                this.setState({
                    transcoding: true
                });
            }, 800);
        }
    };

    handleUploadFailed = () => {
        alertify.error('Upload failed.');
    };

    handleUploadSuccess = () => {
    };

    handleUploadComplete = () => {
        var uploads = this.state.uploads.slice();
        uploads.shift();
        clearTimeout(this.transcodingTimeout);
        this.transcodingTimeout = null;
        this.setState({
            uploads: uploads,
            uploadProgress: 0,
            transcoding: false
        }, () => {
            if (this.state.uploads.length > 0){
                this.sync(false, ()=>{
                    this.startUpload();
                });
            }
        });
    };

    handleRequestUpload = (files) => {
        var uploads = this.state.uploads.slice();
        for (let i = 0, len = files.length; i < len; i++){
            uploads.push(files[i]);
        }
        this.setState({
            uploads: uploads
        }, () => {
            if (this.state.uploads.length >= 1){
                this.startUpload();
            }
        });
    };

    setDescriptionExpanded(expanded){
        this.setState({
            descriptionExpanded: expanded
        });
        if (!expanded){
            this.checkDescriptionOverflow();
        }
    }

    checkDescriptionOverflow = () => {
        if (this.descriptionElement && !this.state.descriptionExpanded){
            if (!this.state.descriptionCanExpand &&
                this.descriptionElement.scrollWidth > this.descriptionElement.clientWidth)
            {
                this.setState({
                    descriptionCanExpand: true
                });
            }else if (this.state.descriptionCanExpand &&
                app.currentRoom.description.indexOf('\n') === -1 &&
                this.descriptionElement.scrollWidth == this.descriptionElement.clientWidth)
            {
                this.setState({
                    descriptionCanExpand: false
                });
            }
        }
    }

    handleDescriptionRef = (el) => {
        this.descriptionElement = el;
        this.checkDescriptionOverflow()
    }

    handleEnded = () => {
        this.sync(false);
    }

    handleProgress = (progress) => {
        if (this.state.queue.length && !this.state.preloadTrack && progress > 0.9){
            this.setState({
                preloadTrack: this.state.queue[0]
            });
        }
    }

    render() {
        const {
            loading, queue, users, currentTrack, seek,
            descriptionExpanded, descriptionCanExpand,
            uploadProgress, uploads, transcoding,
            preloadTrack
        } = this.state;

        const isOwner = app.currentRoom.owner.name == app.currentUser.name;

        var description = app.currentRoom.description;
        if (descriptionCanExpand){
            if (!descriptionExpanded){
                let firstNl = description.indexOf('\n');
                description = description.slice(0, firstNl);
            }else{
                let lines = description.split('\n');
                description = (
                    <div>
                        {
                            lines.map((v) => (
                                <div>{v}</div>
                            ))
                        }
                    </div>
                );
            }
        }

        if (loading){
            return (
                <div className="text-center">
                    <h3><i className="spinner" /> Joining Room...</h3>
                </div>
            )
        }else{
            return (
                <div className="container">
                    <div className="row">
                        <div className="col-sm-10 col-sm-offset-1 text-center">
                            <h2>
                                {app.currentRoom.title} {
                                    !_.isNil(app.currentRoom.isSaved) && (
                                        <SaveRoom room={app.currentRoom} style={{fontSize: '0.66em'}} />
                                    )
                                }
                            </h2>
                            <h5 className="text-muted">
                                Owner: { isOwner ? 'You' : (
                                        <a href={'/user/' + app.currentRoom.owner.name}>
                                            {app.currentRoom.owner.displayName}
                                        </a>
                                    )
                                } {
                                    isOwner && (
                                        <a href={`/room/${app.currentRoom.name}/settings`}
                                            className="btn btn-xs btn-default" >
                                            Settings
                                        </a>
                                    )
                                }

                            </h5>
                            <div>
                                <div ref={this.handleDescriptionRef}
                                    style={descriptionExpanded ? {} : {
                                        display: 'inline-block',
                                        width: '100%',
                                        overflow: 'hidden',
                                        textOverflow: 'ellipsis',
                                        whiteSpace: 'nowrap'
                                    }}>
                                    { description }
                                </div>
                                {
                                    descriptionCanExpand && (
                                        <div>
                                            <a href="javascript:"
                                                onClick={() => this.setDescriptionExpanded(!descriptionExpanded)}>
                                                {descriptionExpanded ? 'Show Less' : 'Show More'}
                                            </a>
                                        </div>
                                    )
                                }
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div className="row">
                        <div className="col-sm-10 col-sm-offset-1">
                            {
                                <Player track={currentTrack} seek={seek}
                                    onEnded={this.handleEnded}
                                    onProgress={this.handleProgress}
                                    preloadTrack={preloadTrack} />
                            }
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-sm-6 col-sm-offset-1">
                            <div className="panel panel-default queue-panel">
                                <div className="panel-heading">
                                    <AddTrackButton className="pull-right"
                                        buttonClass="btn-xs"
                                        onRequestUpload={this.handleRequestUpload} />
                                    <div className="panel-title">Queue</div>
                                </div>
                                <Queue tracks={queue} onRequestRemove={this.handleRequestRemove}/>
                            </div>
                        </div>
                        <div className="col-sm-4">
                            <div className="panel panel-default users-panel">
                                <div className="panel-heading">
                                    <div className="panel-title">
                                        Users ({users.length})
                                    </div>
                                </div>
                                <UserList users={users}/>
                            </div>
                        </div>
                    </div>
                    {
                        (uploads.length && !_.isNil(uploadProgress)) ? (
                            <div className="navbar navbar-default navbar-fixed-bottom">
                                <style>{`
                                    body { padding-bottom: 70px; }
                                `}</style>
                                <div className="container">
                                    <div>
                                        Uploading {uploads[0].name} {
                                            (uploads.length > 1) && (
                                                <span>( {uploads.length-1} in queue )</span>
                                            )
                                        }
                                    </div>
                                    <div className="progress">
                                        {
                                            !transcoding ? (
                                                <div className="progress-bar" role="progressbar"
                                                    aria-valuenow={uploadProgress * 100} aria-valuemin="0" aria-valuemax="100"
                                                    style={{width: (uploadProgress * 100) + '%'}}>
                                                    {Math.floor(uploadProgress * 100)}%
                                                </div>
                                            ) : (
                                                <div className="progress-bar progress-bar-success progress-bar-striped active" role="progressbar"
                                                    aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                    style={{width: '100%'}}>
                                                    Transcoding...
                                                </div>
                                            )
                                        }

                                    </div>
                                </div>
                            </div>
                        ) : null
                    }
                </div>
            );
        }
    }
}

export default Room;
