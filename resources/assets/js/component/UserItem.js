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
                    <a href={'/user/' + username} className="media-heading">{user ? user.displayName : username}</a>
                </div>
            </div>
        );
    }
}

export default UserItem;
