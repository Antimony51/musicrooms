import React from 'react';

class ManageFriend extends React.Component {
    constructor(props){
        super(props);

        this.state = {
            user: props.user || {
                name: props.name,
                displayName: props.displayName,
                friendStatus: props.friendStatus
            },
            waiting: false
        }
    }

    realUpdate (action){
        $.ajax({
            url: '/user/' + this.state.user.name + '/' + action,
            method: 'POST',
        })
            .done((data) => {
                this.setState({
                    user: data
                });
            })
            .fail(() => {
                alertify.error('Action failed.')
            })
            .always(() => {
                this.setState({
                    waiting: false
                })
            });
    }

    update (action){
        if (action === 'removefriend'){
            alertify.confirm('Remove Friend',
                'Are you sure you want to unfriend ' + this.state.user.displayName + '?',
                (value) => {
                    this.realUpdate(action);
                });
        }else if (action === 'cancelrequest'){
            alertify.confirm('Cancel Friend Request',
                'Are you sure you want to cancel the friend request to ' + this.state.user.displayName + '?',
                (value) => {
                    this.realUpdate(action);
                });
        }else{
            this.realUpdate(action);
        }
    }

    render () {
        var user = this.state.user;

        if (!user.friendStatus){
            return null;
        }

        var compact = (this.props.compact > 0 || this.props.compact === 'true') ? true : false;
        var className = compact ? 'btn-xs' : '';

        if (this.state.waiting){
            return (
                <i className={ 'spinner' + compact ? '' : 'spinner-large' } />
            );
        }else if (user.friendStatus === 'friend'){
            return (
                <button type="button" className={'btn btn-danger removefriend '+className} onClick={()=>this.update('removefriend')}>
                    {compact ? "Remove" : "Remove Friend"}
                </button>
            );
        }else if (user.friendStatus === 'request_sent'){
            return (
                <button type="button" className={'btn btn-info requestsent '+className} onClick={()=>this.update('cancelrequest')}>
                    Request Sent
                </button>
            );
        }else if (user.friendStatus === 'request_received'){
            return (
                <span>
                    <button type="button" className={'btn btn-success acceptfriend '+className} onClick={()=>this.update('acceptfriend')}>
                        { compact ? "Accept" : "Accept Request"}
                    </button>
                    <button type="button" className={'btn btn-danger declinefriend '+className} onClick={()=>this.update('declinefriend')}>
                        { compact ? "Decline" : "Decline Request"}
                    </button>
                </span>
            );
        }else if (user.friendStatus === 'can_add'){
            return (
                <button type="button" className={'btn btn-success addfriend '+className} onClick={()=>this.update('addfriend')}>
                    { compact ? "Add" : "Add Friend" }
                </button>
            );
        }else{
            return null;
        }
    }
}

export default ManageFriend;
