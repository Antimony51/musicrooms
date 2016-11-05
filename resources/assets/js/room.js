import React from 'react';
import ReactDOM from 'react-dom';
import Room from './component/Room';

var content = document.getElementById('content');
var unmount = function (){
  return ReactDOM.unmountComponentAtNode(content);
}
ReactDOM.render(<Room room={app.currentRoom} unmount={unmount}/>, content);
