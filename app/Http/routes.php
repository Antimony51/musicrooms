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
});

Route::get('admin/users', 'UserController@showUserList');
Route::get('admin/rooms', 'RoomController@showAllRooms');

Route::get('rooms', 'RoomController@showPublicRooms')->name('publicRooms');
Route::group(['prefix' => 'room/{room}'], function(){
    Route::get('', 'RoomController@show')->name('room');
    Route::get('syncme', 'RoomController@syncMe')->name('syncMe');
    Route::get('join', 'RoomController@join')->name('joinRoom');
    Route::get('leave', 'RoomController@leave')->name('leaveRoom');
});

Route::group(['middleware' => 'auth'], function(){
    Route::group(['prefix' => 'user/{user}'], function(){
        Route::post('favorites/add/{id}', 'UserController@addFavorite');
        Route::post('favorites/remove/{id}', 'UserController@removeFavorite');
        Route::post('addfriend', 'UserController@addFriend');
        Route::post('removefriend', 'UserController@removeFriend');
        Route::post('acceptfriend', 'UserController@acceptFriend');
        Route::post('declinefriend', 'UserController@declineFriend');
        Route::post('updateprofile', 'UserController@updateProfile')->name('updateProfile');
        Route::get('edit', 'UserController@showEditProfile')->name('editProfile');
    });

    Route::get('rooms/saved', 'RoomController@showSavedRooms')->name('savedRooms');
    Route::get('rooms/mine', 'RoomController@showMyRooms')->name('myRooms');
});