<?php

Route::get('/room/{name}', 'RoomController@show');
Route::auth();

Route::get('/home', 'HomeController@index');
Route::get('/', 'HomeController@index');

Route::get('/user/{name}', 'ProfileController@showOverview')->name('profile');
Route::get('/user/{name}/overview', 'ProfileController@showOverview')->name('profileOverview');
Route::get('/user/{name}/favorites', 'ProfileController@showFavorites')->name('profileFavorites');
Route::get('/user/{name}/friends', 'ProfileController@showFriends')->name('profileFriends');

Route::get('/admin/userlist', 'AdminController@showUserList');