var React = require('react');
var ReactDOM = require('react-dom');
var UserToken = require('./components/UserToken');

var userData = {};
var trackData = {};
var roomState = null;

var $queuePanel = $('.queue-panel');
var $usersPanel = $('.users-panel');

function queueItemHtml(track){
    return `<div class="panel panel-default">
                <div class="panel-body">
                    <span class="title">${_.escape(track.title)}</span> - <span class="artist">${_.escape(track.artist)}</span><br>
                    <span class="duration">${durationString(track.duration)}</span>
                </div>
            </div>`;
}

function manageFriendHtml(user, compact){
    return '';
}

function userTokenHtml(user) {
    return `<div class="panel panel-default user-token">
                <div class="panel-body">
                    <div class="media">
                        <div class="media-left">
                            <a href="/user/${user.name}">
                                <img src="${user.iconSmall}" alt="Profile Picture"
                                 style="width: 48px; height: 48px;">
                            </a>
                        </div>
                        <div class="media-body">
                            <a href="/user/${user.name}" class="media-heading">${_.escape(user.displayName)}</a>
                            ${app.currentUser ? '<br>' + manageFriendHtml(user, true) : ''}
                        </div>
                    </div>
                </div>
            </div>`;
}

function updateUI (){
    $queuePanel.empty();
    $usersPanel.empty();

    for (let i = 0; i < roomState.queue.length; i++){
        let trackState = roomState.queue[i];
        let track = trackData[trackState.trackId];
        let $queueToken = $(queueTokenHtml(track));
        $queuePanel.append($queueToken);
    }

    for (let i=0; i< roomState.users.length; i++){
        let userId = roomState.users[i];
        let user = userData[userId];
        let $userToken = $(userTokenHtml(user));
        $usersPanel.append($userToken);
    }
}

function processStateChange (newState){
    var usersAdded = [];
    var queueAdded = [];
    if (!roomState){
        usersAdded = newState.users;
        queueAdded = newState.queue;
    }else{
        if (newState.users_timestamp != roomState.users_timestamp){
            usersAdded = newState.users.filter((item) => {
                return !roomState.users.includes(item);
            });
        }

        if (newState.queue_timestamp != roomState.queue_timestamp){
            queueAdded = newState.queue.filter((item) => {
                return !roomState.queue.includes(item);
            });
        }
    }

    roomState = newState;

    var getData = {
        _token: app.csrf_token
    };

    if (usersAdded.length > 0){
        getData.users = usersAdded;
    }
    if (queueAdded.length > 0){
        getData.tracks = queueAdded.map((item) => {
            return item.trackId;
        });
    }

    if (getData.users || getData.tracks) {
        $.ajax({
            url: `/room/${app.currentRoom.name}/getdata`,
            method: 'post',
            dataType: 'json',
            data: JSON.stringify(getData),
            contentType: 'application/json; charset=utf-8',
        })
            .done(function (data) {
                if (data.users) {
                    for (let user of data.users) {
                        userData[user.name] = user;
                    }
                }
                if (data.tracks) {
                    for (let track of data.tracks) {
                        trackData[track.id] = track;
                    }
                }
                updateUI();
            });
    }else{
        updateUI();
    }
}

var syncInterval = null;

function roomInit(callback) {
    var retries = 0;
    $.ajax({
        url: `/room/${app.currentRoom.name}/join`,
        method: 'post',
        dataType: 'json',
        data: {
            _token: app.csrf_token
        }
    })
        .done(function (data) {
            processStateChange(data);
            callback(true);
            syncInterval = setInterval(roomSync, 1000);
        })
        .fail(function(){
            if (retries < 4){
                retries++;
                setTimeout(roomInit, 1000);
            }else{
                callback(false);
            }

        });
}

var syncFails = 0;

function roomSync() {
    $.ajax({
        url: `/room/${app.currentRoom.name}/syncme`,
        method: 'get',
        dataType: 'json',
        data: {
            _token: app.csrf_token
        }
    })
        .done(function(data){
            syncFails = 0;
            processStateChange(data);
        })
        .fail(function(){
            syncFails++;
            if (syncFails == 10){
                clearInterval(syncInterval);
                bootbox.alert('Connection lost', function(){
                    location = "/rooms";
                });
            }
        })
}

function roomUnload(){
    $.ajax({
        url: `/room/${app.currentRoom.name}/leave`,
        method: 'post',
        data: {
            _token: app.csrf_token
        }

    });
}

$(document).ready(function(){
    var joiningMsg = bootbox.dialog({
        message: `
            <div class="text-center">
                <h3><i class="spinner spinner"></i> Joining Room...</h3>
            </div>`,
        closeButton: false
    });
    roomInit(function(result){
        joiningMsg.modal('hide');
        if (!result){
            booxbox.alert('Failed to join room', function(){
                 location = '/rooms';
            });
        }
    });
});

window.addEventListener('beforeunload', function(){
    roomUnload();
});
