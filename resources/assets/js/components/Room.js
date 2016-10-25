var React = require('react');
var UserList = require('./UserList');

module.exports = class Room extends React.Component {

    private syncInterval = null;
    private syncFails = 0;

    constructor(props) {
        super(props);

        this.state = null;

        // Bind this to event handlers
        this.handleBeforeUnload = this.handleBeforeUnload.bind(this);
    }

    handleBeforeUnload(){
        $.ajax({
            url: `/room/${this.props.room.name}/leave`,
            method: 'post',
            data: {
                _token: app.csrf_token
            }
        });
    }

    sync(){
        $.ajax({
            url: `/room/${this.props.room.name}/syncme`,
            method: 'get',
            dataType: 'json'
        })
            .done((data) => {
                this.syncFails = 0;
            })
            .fail(() => {
                this.syncFails++;
                if (this.syncFails == 10){
                    clearInterval(this.syncInterval);
                    bootbox.alert('Connection lost', function(){
                        location = "/rooms";
                    });
                }
            });
    }

    componentDidMount(){
        $.ajax({
            url: `/room/${this.props.room.name}/join`,
            method: 'post',
            dataType: 'json',
            data: {
                _token: app.csrf_token
            }
        })
            .done((data) => {
                this.setState(data);
                window.addEventListener('onbeforeunload', this.handleBeforeUnload);
                this.syncInterval = setInterval(this.sync, 1000);
            });
    }

    componentWillUnmount(){
        window.removeEventListener('onbeforeunload', this.handleBeforeUnload);
        clearInterval(this.syncInterval);
    }

    render() {
        var room = this.props.room;
        if (!this.state){
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
                            <div className="panel panel-default">
                                <div className="panel-body player-panel">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-4">
                            <div className="panel panel-default">
                                <div className="panel-body queue-panel">
                                </div>
                            </div>
                            <button className="btn btn-default">Add Track</button>
                        </div>
                        <div className="col-md-4">
                            <div className="panel panel-default">
                                <div className="panel-body chat-panel">
                                </div>
                            </div>
                        </div>
                        <div className="col-md-4">
                            <div className="panel panel-default">
                                <div className="panel-body users-panel">
                                    <UserList users={this.state.users}/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            );
        }
    }
};