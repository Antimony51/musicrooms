<?php

Route::get('/room/{name}', 'RoomController@show');
Route::auth();

Route::get('/home', 'HomeController@index');
Route::get('/', 'HomeController@index');

Route::group(['prefix' => 'user/{user}'], function(){
    Route::get('', 'UserController@showOverview')->name('profile');
    Route::get('overview', 'UserController@showOverview')->name('profileOverview');
    Route::get('favorites', 'UserController@showFavorites')->name('profileFavorites');
    Route::get('friends', 'UserController@showFriends')->name('profileFriends');
});

Route::get('/admin/userlist', 'AdminController@showUserList');

Route::group(['middleware' => 'auth'], function(){
    Route::group(['prefix' => 'user/{user}'], function(){
        Route::post('favorites/add/{id}', 'UserController@addFavorite');
        Route::post('favorites/remove/{id}', 'UserController@removeFavorite');
        Route::post('addfriend', 'UserController@addFriend');
        Route::post('removefriend', 'UserController@removeFriend');
        Route::post('acceptfriend', 'UserController@acceptFriend');
        Route::post('declinefriend', 'UserController@declineFriend');
        Route::post('updateprofile', 'UserController@updateProfile')->name('updateProfile');
        Route::get('edit', 'UserController@showEditProfile')->name('showEditProfile');
    });
});