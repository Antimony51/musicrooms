<?php
Route::auth();

Route::get('home', 'HomeController@index');
Route::get('', 'HomeController@index');

Route::group(['prefix' => 'user/{user}'], function(){
    Route::get('', 'UserController@showOverview')->name('user');
    Route::get('overview', 'UserController@showOverview')->name('profileOverview');
    Route::get('favorites', 'UserController@showFavorites')->name('profileFavorites');
    Route::get('friends', 'UserController@showFriends')->name('profileFriends');
    Route::get('rooms', 'UserController@showRooms')->name('profileRooms');
    Route::get('data', function(App\User $user) {return $user;});
});

Route::get('admin/users', 'UserController@showUserList');
Route::get('admin/rooms', 'RoomController@showAllRooms');

Route::get('rooms', 'RoomController@showPublicRooms')->name('publicRooms');
Route::group(['prefix' => 'room/{room}'], function(){
    Route::get('', 'RoomController@show')->name('room');
    Route::get('syncme', 'RoomController@syncMe')->name('syncMe');
    Route::post('join', 'RoomController@join')->name('joinRoom');
    Route::post('leave', 'RoomController@leave')->name('leaveRoom');
    Route::post('addtrack', 'RoomController@addTrack');
    Route::post('removetrack', 'RoomController@removeTrack');
    Route::get('data', function(App\Room $room) {return $room;});
    Route::get('getdata', 'RoomController@getData');
});

Route::get('track/{track}/data', function(App\Track $track) {return $track;});

Route::group(['middleware' => 'auth'], function(){
    Route::group(['prefix' => 'user/{user}'], function(){
        Route::post('favorites/add/{id}', 'UserController@addFavorite');
        Route::post('favorites/remove/{id}', 'UserController@removeFavorite');
        Route::get('favorites/search', 'UserController@searchFavorites');
        Route::post('savedrooms/add/{room}', 'UserController@addSavedRoom');
        Route::post('savedrooms/remove/{room}', 'UserController@removeSavedRoom');
        Route::post('addfriend', 'UserController@addFriend');
        Route::post('removefriend', 'UserController@removeFriend');
        Route::post('cancelrequest', 'UserController@removeFriend');
        Route::post('acceptfriend', 'UserController@acceptFriend');
        Route::post('declinefriend', 'UserController@declineFriend');
        Route::post('updateprofile', 'UserController@updateProfile')->name('updateProfile');
        Route::get('edit', 'UserController@showEditProfile')->name('editProfile');
    });

    Route::get('rooms/saved', 'RoomController@showSavedRooms')->name('savedRooms');
    Route::get('rooms/mine', 'RoomController@showMyRooms')->name('myRooms');
    Route::get('rooms/create', 'RoomController@showCreateRoom');
    Route::post('rooms/create', 'RoomController@createRoom')->name('createRoom');
});
