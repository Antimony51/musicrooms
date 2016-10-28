var React = require('react');
var List = require('./List');
var User = require('./User');

module.exports = class UserList extends React.Component {
    render() {
        return (
            <ul className="list-group">
                {
                    this.props.users.map((user) => (
                        <li className="list-group-item" key={_.isString(user) ? user : user.name}>
                            <User user={user} />
                        </li>
                    ))
                }
            </ul>
        );
    }
};
