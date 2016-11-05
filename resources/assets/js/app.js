require('babel-polyfill');
window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');
window.alertify = require('alertifyjs');
window._ = require('lodash');
window.PubSub = require('pubsub-js');
window.URI = require('urijs');
window.moment = require('moment');

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

$( document ).ready(function() {
    console.log($.fn.tooltip.Constructor.VERSION);

    $('.favorite-heart').click(function (ev){
        var $target = $(ev.target);
        var trackId = $target.attr('data-track-id');

        if ($target.hasClass('checked') && !$target.hasClass('spinner')){
            $target.addClass('spinner');
            $.ajax({
                url: '/user/' + app.currentUser.name + '/favorites/remove/' + trackId,
                method: 'POST'
            })
                .done(function(){
                    $target.removeClass('checked');
                })
                .fail(function(){
                    $target.addClass('checked');
                    alertify.error('Removing favorite failed.');
                })
                .always(function(){
                    $target.removeClass('spinner');
                });
        }else{
            $target.addClass('spinner');
            $.ajax({
                url: '/user/' + app.currentUser.name + '/favorites/add/' + trackId,
                method: 'POST',
            })
                .done(function(){
                    $target.addClass('checked');
                })
                .fail(function(){
                    $target.removeClass('checked');
                    alertify.error('Adding favorite failed.');
                })
                .always(function(){
                    $target.removeClass('spinner');
                });
        }
    });

    $('.manage-friend button').click(function(ev){
        var $target = $(ev.target);
        var $container = $target.closest('.manage-friend');
        var $spinner = $target.siblings('.spinner');
        console.log($spinner);
        var user = $container.attr('data-user');
        var displayName = _.escape($container.attr('data-display-name'));

        if ($spinner.hasClass('hidden')) {
            if ($target.hasClass('addfriend')) {
                $spinner.removeClass('hidden');
                $.ajax({
                    url: '/user/' + user + '/addfriend',
                    method: 'POST',
                })
                    .done(function(){
                        $target.addClass('hidden');
                        $container.find('.requestsent').removeClass('hidden');
                    })
                    .fail(function(){
                        alertify.error('Adding friend failed.');
                    })
                    .always(function(){
                        $spinner.addClass('hidden');
                    });
            } else if ($target.hasClass('removefriend')) {
                alertify.confirm('Remove Friend',
                    'Are you sure you want to unfriend ' + displayName + '?',
                    function () {
                        $spinner.removeClass('hidden');
                        $.ajax({
                            url: '/user/' + user + '/removefriend',
                            method: 'POST',
                        })
                            .done(function () {
                                $target.addClass('hidden');
                                $container.find('.addfriend').removeClass('hidden');
                            })
                            .fail(function () {
                                alertify.error('Removing friend failed.');
                            })
                            .always(function () {
                                $spinner.addClass('hidden');
                            });
                    },
                    ()=>{}
                );
            } else if ($target.hasClass('requestsent')) {
                alertify.confirm('Cancel Friend Request',
                    'Are you sure you want to cancel the friend request to ' + displayName + '?',
                    function(){
                        $spinner.removeClass('hidden');
                        $.ajax({
                            url: '/user/' + user + '/removefriend',
                            method: 'POST',
                        })
                            .done(function () {
                                $target.addClass('hidden');
                                $container.find('.addfriend').removeClass('hidden');
                            })
                            .fail(function () {
                                alertify.error('Cancel request failed.');
                            })
                            .always(function () {
                                $spinner.addClass('hidden');
                            });
                    },
                    ()=>{}
                );
            } else if ($target.hasClass('acceptfriend')) {
                $spinner.removeClass('hidden');
                $.ajax({
                    url: '/user/' + user + '/acceptfriend',
                    method: 'POST',
                })
                    .done(function(){
                        $target.addClass('hidden');
                        $container.find('.declinefriend').addClass('hidden');
                        $container.find('.removefriend').removeClass('hidden');
                    })
                    .fail(function(){
                        alertify.error('Accepting friend request failed.');
                    })
                    .always(function(){
                        $spinner.addClass('hidden');
                    });
            } else if ($target.hasClass('declinefriend')) {
                $spinner.removeClass('hidden');
                $.ajax({
                    url: '/user/' + user + '/declinefriend',
                    method: 'POST',
                })
                    .done(function(){
                        $target.addClass('hidden');
                        $container.find('.acceptfriend').addClass('hidden');
                        $container.find('.addfriend').removeClass('hidden');
                    })
                    .fail(function(){
                        alertify.error('Declining friend request failed.');
                    })
                    .always(function(){
                        $spinner.addClass('hidden');
                    });
            }
        }
    });
});
