import React from 'react';
import User from './UserItem';

function UserList (props) {
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

export default UserList;
