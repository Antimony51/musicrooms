var React = require('react');
var List = require('./List');
var UserToken = require('./UserToken');

module.exports = class UserList extends React.Component {
    render() {
        return <List items={
            this.props.users.map((username) => (
                <UserToken user={username} />
            ))
        } />;
    }
};