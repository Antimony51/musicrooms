import React from 'react';

class UserItem extends React.Component {
    static propTypes = {
        user: React.PropTypes.oneOfType([
            React.PropTypes.string,
            React.PropTypes.shape({
                name: React.PropTypes.string,
                displayName: React.PropTypes.string,
                iconSmall: React.PropTypes.string
            })
        ])
    };

    constructor(props) {
        super(props);
        if (_.isString(props.user)){
            this.state = {
                username: props.user,
                user: null
            }
        }else{
            this.state = {
                username: props.user.name,
                user: props.user
            };
        }
    }

    componentWillReceiveProps(nextProps){
        if (_.isString(nextProps.user)){
            this.setState({
                username: nextProps.user,
                user: null
            });
        }else{
            this.setState({
                username: nextProps.user.name,
                user: nextProps.user
            });
        }
    }

    render() {
        var username = this.state.username;
        var user = this.state.user;

        return (
            <div className="media">
                <div className="media-left">
                    <a href={'/user/' + username} className="user-icon-small">
                    {
                        <img src={ user ? user.iconSmall : "/img/noprofileimg_small.png"} alt="Profile Picture"/>
                    }
                    </a>
                </div>
                <div className="media-body media-middle">
                    <span className="media-heading">
                        <a href={'/user/' + username}>{user ? user.displayName : username}</a> {
                            app.currentRoom && user && app.currentRoom.owner && app.currentRoom.owner.name == user.name ?
                                <span className="color-darkgreen user-role" title="Room Owner">[O]</span>
                            : null
                        } {
                            user && user.admin ?
                                <span className="color-red user-role" title="Admin">[A]</span>
                            : null
                        }
                    </span>
                </div>
            </div>
        );
    }
}

export default UserItem;
