window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');
window.alertify = require('alertifyjs');
window.bootbox = require('bootbox');

window._ = require('lodash/core');
window._.escape = require('lodash/escape');

window.PubSub = require('pubsub-js');

function leftPad(n, width, z) {
    z = z || '0';
    n = n + '';
    return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

function durationString($duration){
    var hour = Math.floor($duration/216000);
    $duration -= hour;
    var min = Math.floor($duration/3600);
    $duration -= min;
    var sec = Math.floor($duration/60);
    return ((hour > 0) ? (hour + ':' + leftPad(min, 2)) : min) + ':' + leftPad(sec, 2);
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
                method: 'POST',
                data: {'_token': app.csrf_token}
            })
                .done(function(){
                    $target.removeClass('checked');
                })
                .fail(function(){
                    $target.addClass('checked');
                    alertify.error("Removing favorite failed.")
                })
                .always(function(){
                    $target.removeClass('spinner');
                });
        }else{
            $target.addClass('spinner');
            $.ajax({
                url: '/user/' + app.currentUser.name + '/favorites/add/' + trackId,
                method: 'POST',
                data: {'_token': app.csrf_token}
            })
                .done(function(){
                    $target.addClass('checked');
                })
                .fail(function(){
                    $target.removeClass('checked');
                    alertify.error("Adding favorite failed.")
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
                    data: {'_token': app.csrf_token}
                })
                    .done(function(){
                        $target.addClass('hidden');
                        $container.find('.requestsent').removeClass('hidden');
                    })
                    .fail(function(){
                        alertify.error("Adding friend failed.");
                    })
                    .always(function(){
                        $spinner.addClass('hidden');
                    });
            } else if ($target.hasClass('removefriend')) {
                bootbox.confirm('Are you sure you want to unfriend ' + displayName + '?',
                    function (value) {
                        if (value) {
                            $spinner.removeClass('hidden');
                            $.ajax({
                                url: '/user/' + user + '/removefriend',
                                method: 'POST',
                                data: {'_token': app.csrf_token}
                            })
                                .done(function () {
                                    $target.addClass('hidden');
                                    $container.find('.addfriend').removeClass('hidden');
                                })
                                .fail(function () {
                                    alertify.error("Removing friend failed.")
                                })
                                .always(function () {
                                    $spinner.addClass('hidden');
                                });
                        }
                    }
                );
            } else if ($target.hasClass('requestsent')) {
                bootbox.confirm('Are you sure you want to cancel the friend request to ' + displayName + '?',
                    function (value) {
                        if (value) {
                            $spinner.removeClass('hidden');
                            $.ajax({
                                url: '/user/' + user + '/removefriend',
                                method: 'POST',
                                data: {'_token': app.csrf_token}
                            })
                                .done(function () {
                                    $target.addClass('hidden');
                                    $container.find('.addfriend').removeClass('hidden');
                                })
                                .fail(function () {
                                    alertify.error("Cancel request failed.")
                                })
                                .always(function () {
                                    $spinner.addClass('hidden');
                                });
                        }
                    }
                );
            } else if ($target.hasClass('acceptfriend')) {
                $spinner.removeClass('hidden');
                $.ajax({
                    url: '/user/' + user + '/acceptfriend',
                    method: 'POST',
                    data: {'_token': app.csrf_token}
                })
                    .done(function(){
                        $target.addClass('hidden');
                        $container.find('.declinefriend').addClass('hidden');
                        $container.find('.removefriend').removeClass('hidden');
                    })
                    .fail(function(){
                        alertify.error("Accepting friend request failed.")
                    })
                    .always(function(){
                        $spinner.addClass('hidden');
                    });
            } else if ($target.hasClass('declinefriend')) {
                $spinner.removeClass('hidden');
                $.ajax({
                    url: '/user/' + user + '/declinefriend',
                    method: 'POST',
                    data: {'_token': app.csrf_token}
                })
                    .done(function(){
                        $target.addClass('hidden');
                        $container.find('.acceptfriend').addClass('hidden');
                        $container.find('.addfriend').removeClass('hidden');
                    })
                    .fail(function(){
                        alertify.error("Declining friend request failed.")
                    })
                    .always(function(){
                        $spinner.addClass('hidden');
                    });
            }
        }
    });
});