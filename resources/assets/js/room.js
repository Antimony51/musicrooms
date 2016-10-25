var React = require('react');
var ReactDOM = require('react-dom');
var Room = require('./components/Room');

ReactDOM.render(<Room room={app.currentRoom}/>, document.getElementById('content'));