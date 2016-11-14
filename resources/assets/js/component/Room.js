import React from 'react';
import UserList from './UserList';
import AddTrackButton from './AddTrackButton';
import Queue from './Queue';
import Player from './Player';

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
            this.state.seek !== nextState.seek
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
    }

    componentWillUnmount(){
        window.removeEventListener('beforeunload', this.handleBeforeUnload);
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

    render() {
        const {
            loading, queue, users, currentTrack, seek
        } = this.state;
        if (loading){
            return (
                <div className="text-center">
                    <h3><i className="spinner spinner" /> Joining Room...</h3>
                </div>
            )
        }else{
            return (
                <div className="container">
                    <div className="row">
                        <div className="col-md-12">
                            <div className="panel panel-default player-panel">
                                <div className="panel-body">
                                    {
                                        <Player track={currentTrack} seek={seek} />
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-4">
                            <div className="panel panel-default queue-panel">
                                <div className="panel-heading">
                                    <AddTrackButton className="pull-right" buttonClass="btn-xs" />
                                    <div className="panel-title">Queue</div>
                                </div>
                                <Queue tracks={queue} onRequestRemove={this.handleRequestRemove}/>
                            </div>
                        </div>
                        <div className="col-md-4">
                            <div className="panel panel-default chat-panel">
                                <div className="panel-heading">
                                    <div className="panel-title">Chat</div>
                                </div>
                                <div className="panel-body">
                                </div>
                            </div>
                        </div>
                        <div className="col-md-4">
                            <div className="panel panel-default users-panel">
                                <div className="panel-heading">
                                    <div className="panel-title">Users</div>
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
