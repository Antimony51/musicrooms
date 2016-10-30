var React = require('react');
var User = require('./UserItem');

module.exports = function UserList (props) {
    return (
        <ul className="list-group">
            {
                props.users.map((user) => (
                    <li className="list-group-item" key={_.isString(user) ? user : user.name}>
                        <User user={user} />
                    </li>
                ))
            }
        </ul>
    );
};
