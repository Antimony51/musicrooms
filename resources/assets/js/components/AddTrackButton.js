var React = require('react');
var Modal = require('react-modal');

module.exports = class UserList extends React.Component {

  constructor(props){
    super(props);
  }

  handleClick = () => {
    bootbox.dialog({
      
    })
  }

  render () {
    return (
      <button className="btn btn-default" onClick={this.handleClick}>Add Track</button>
    );
  }
}
