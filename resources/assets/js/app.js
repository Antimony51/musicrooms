require('babel-polyfill');
window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');
window.alertify = require('alertifyjs');
window._ = require('lodash');
window.PubSub = require('pubsub-js');
window.URI = require('urijs');
window.moment = require('moment');

import React from 'react';
import ReactDOM from 'react-dom';
import ManageFriend from './component/ManageFriend';
import FavoriteHeart from './component/FavoriteHeart';
import CopyLink from './component/CopyLink';

$.ajaxSetup({
    data: {
        _token: app.csrf_token
    }
});

window.durationString = function(duration){
    var hour = Math.floor(duration/3600);
    duration -= hour*3600;
    var min = Math.floor(duration/60);
    duration -= min*60;
    var sec = Math.floor(duration);
    return ((hour > 0) ? (hour + ':' + _.padStart(min, 2, '0')) : min) + ':' + _.padStart(sec, 2, '0');
}

window.copyLink = function (link){
    alertify.prompt('Copy Link', '', link, ()=>{}, ()=>{});
}

$( document ).ready(function() {
    console.log($.fn.tooltip.Constructor.VERSION);

    $('.favorite-heart-root').each(function (i, v){
        var $v = $(v);
        ReactDOM.render(<FavoriteHeart trackId={$v.attr('data-track-id')} checked={$v.attr('data-checked')} />, v);
    });

    $('.copy-link-root').each(function (i, v){
        var $v = $(v);
        ReactDOM.render(<CopyLink link={$v.attr('data-link')} />, v);
    });

    $('.manage-friend-root').each(function(i, v){
        var $v = $(v);
        ReactDOM.render(
            <ManageFriend
                name={$v.attr('data-name')}
                displayName={$v.attr('data-display-name')}
                friendStatus={$v.attr('data-friend-status')}
                compact={$v.attr('data-compact')}
            />, v);
    });
});
