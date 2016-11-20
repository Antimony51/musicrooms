import React from 'react';
import UserList from './UserList';
import AddTrackButton from './AddTrackButton';
import Queue from './Queue';
import Player from './Player';
import SaveRoom from './SaveRoom';

class Room extends React.Component {

    syncInterval = null;
    syncFails = 0;
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
            seek: 0,
            descriptionExpanded: false,
            descriptionCanExpand: app.currentRoom.description.indexOf('\n') !== -1
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
            this.state.loading !== nextState.loading ||
            this.state.queue.length !== nextState.queue.length ||
            this.state.users.length !== nextState.users.length ||
            this.state.seek !== nextState.seek ||
            this.state.descriptionExpanded !== nextState.descriptionExpanded ||
            this.state.descriptionCanExpand !== nextState.descriptionCanExpand
        ){
            return true;
        }

        for (let i = 0; i < this.state.queue.length; i++){
            if ((this.state.queue[i] ? !nextState.queue[i] : nextState.queue[i]) ||
                this.state.queue[i] && nextState.queue[i] &&
                    this.state.queue[i].key !== nextState.queue[i].key)
            {
                return true;
            }
        }

        for (let i = 0; i < this.state.users.length; i++){
            if (this.state.users[i] !== nextState.users[i]){
                return true;
            }
        }

        return false;
    }

    updateState (){
        var currentTrack = null;
        if (this.receivedState.currentTrack &&
            this.trackData[this.receivedState.currentTrack] &&
            this.userData[this.receivedState.currentTrackMeta.owner])
        {
            currentTrack = _.assign(
                {},
                this.trackData[this.receivedState.currentTrack],
                { owner: this.userData[this.receivedState.currentTrackMeta.owner] }
            );
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

    sync = () => {
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
                if (this.syncFails == 10){
                    this.props.unmount();
                    alertify.alert('Error', 'Connection lost',
                        function(){
                            location = "/rooms";
                        });
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
                this.syncInterval = setInterval(this.sync, 1000);
            })
            .fail(() => {
                this.props.unmount();
                alertify.alert('Error', 'Failed to join',
                    function(){
                        location = "/rooms";
                    });
            });
        window.addEventListener('resize', this.checkDescriptionOverflow);
    }

    componentWillUnmount(){
        window.removeEventListener('beforeunload', this.handleBeforeUnload);
        window.removeEventListener('resize', this.checkDescriptionOverflow);
        clearInterval(this.syncInterval);
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

    render() {
        const {
            loading, queue, users, currentTrack, seek,
            descriptionExpanded, descriptionCanExpand
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
                                <Player track={currentTrack} seek={seek} />
                            }
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-sm-6 col-sm-offset-1">
                            <div className="panel panel-default queue-panel">
                                <div className="panel-heading">
                                    <AddTrackButton className="pull-right" buttonClass="btn-xs" />
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
                </div>
            );
        }
    }
}

export default Room;
