var React = require('react');
var ReactDOM = require('react-dom');
var Room = require('./components/Room');

var content = document.getElementById('content');
var unmount = function (){
  return ReactDOM.unmountComponentAtNode(content);
}
ReactDOM.render(<Room room={app.currentRoom} unmount={unmount}/>, content);
