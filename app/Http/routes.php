<?php

Route::get('/room/{name}', 'RoomController@show');
Route::auth();

Route::get('/home', 'HomeController@index');
Route::get('/', 'HomeController@index');

Route::get('/user/{name}', 'ProfileController@showOverview');