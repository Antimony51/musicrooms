window.$ = window.jQuery = require('jquery');
require('bootstrap-sass');
window.alertify = require('alertifyjs');

$( document ).ready(function() {
    console.log($.fn.tooltip.Constructor.VERSION);

    $('.favorite-heart').click(function (ev){
        var $target = $(ev.target);
        var trackId = $target.attr('data-track-id');

        if ($target.hasClass('checked') && !$target.hasClass('spinner-small')){
            $target.addClass('spinner-small');
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
                    $target.removeClass('spinner-small');
                });
        }else{
            $target.addClass('spinner-small');
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
                    $target.removeClass('spinner-small');
                });
        }
    });

    $('.manage-friend button').click(function(ev){
        var $target = $(ev.target);
        var $container = $target.closest('.manage-friend');
        var $spinner = $target.siblings('.spinner');
        var user = $container.attr('data-user');
        var displayName = $container.attr('data-display-name');

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
                        alertify.error("Adding friend failed.")
                    })
                    .always(function(){
                        $spinner.addClass('hidden');
                    });
            } else if ($target.hasClass('removefriend')) {
                alertify.confirm('Remove Friend', 'Are you sure you want to unfriend ' + displayName + '?', function(){
                    $spinner.removeClass('hidden');
                    $.ajax({
                        url: '/user/' + user + '/removefriend',
                        method: 'POST',
                        data: {'_token': app.csrf_token}
                    })
                        .done(function(){
                            $target.addClass('hidden');
                            $container.find('.addfriend').removeClass('hidden');
                        })
                        .fail(function(){
                            alertify.error("Removing friend failed.")
                        })
                        .always(function(){
                            $spinner.addClass('hidden');
                        });
                });
            } else if ($target.hasClass('acceptfriend')) {
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