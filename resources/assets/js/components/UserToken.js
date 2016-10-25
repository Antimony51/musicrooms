var React = require('react');

module.exports = class UserToken extends React.Component {
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
        if (typeof props.user === 'string'){
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

    componentDidMount(){
        var username = this.state.username;
        if (!this.state.user){
            $.ajax({
                url: `/user/${username}/data`,
                method: 'get',
                dataType: 'json'
            })
                .done((data) => {
                    this.setState({
                        user: data
                    });
                });
        }
    }

    render() {
        var username = this.state.username;
        var user = this.state.user;

        return (
            <div className="panel panel-default user-token">
                <div className="panel-body">
                    <div className="media">
                        <div className="media-left">
                            <a href={'/user/' + username} className="user-icon-small">
                            {
                                <img src={ user ? user.iconSmall : "/img/noprofileimg_small.png"} alt="Profile Picture"/>
                            }
                            </a>
                        </div>
                        <div className="media-body">
                            <a href="/user/${user.name}" className="media-heading">{user ? user.displayName : username}</a>
                            {app.currentUser ? <div>[manage friend]</div> : ''}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
};