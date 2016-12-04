<?php
//Route::auth();
// Authentication Routes...
$this->get('login', 'Auth\AuthController@showLoginForm');
$this->post('login', 'Auth\AuthController@login');
$this->get('logout', 'Auth\AuthController@logout');

// Registration Routes...
$this->get('register', 'Auth\AuthController@showRegistrationForm');
$this->post('register', 'Auth\AuthController@register');

// Password Reset Routes...
$this->get('password/reset/{token?}', 'Auth\PasswordController@showResetForm');
$this->get('password/email', 'Auth\PasswordController@showLinkRequestForm');
$this->post('password/email', 'Auth\PasswordController@sendResetLinkEmail');
$this->post('password/reset', 'Auth\PasswordController@reset');
if (config('auth.passwords.users.use_security_questions')){
    $this->get('password/questions', 'Auth\PasswordController@showQuestionsEmailForm');
    $this->get('password/answers', 'Auth\PasswordController@showAnswersForm');
    $this->post('password/answers', 'Auth\PasswordController@checkAnswers');
}


// Home Routes
Route::get('home', 'HomeController@index');
Route::get('', 'HomeController@index')->name('home');

// Authed routes
Route::group(['middleware' => 'auth'], function(){
    // Admin
    Route::get('admin/users', 'UserController@showUserList');
    Route::get('admin/rooms', 'RoomController@showAllRooms');

    // User
    Route::group(['prefix' => 'user/{user}'], function(){
        Route::get('', 'UserController@showOverview')->name('user');
        Route::get('overview', 'UserController@showOverview')->name('profileOverview');
        Route::get('favorites', 'UserController@showFavorites')->name('profileFavorites');
        Route::get('friends', 'UserController@showFriends')->name('profileFriends');
        Route::get('rooms', 'UserController@showRooms')->name('profileRooms');
        Route::get('data', function(App\User $user) {return $user;});
        Route::get('favorites/search', 'UserController@searchFavorites');
        Route::post('addfriend', 'UserController@addFriend');
        Route::post('removefriend', 'UserController@removeFriend');
        Route::post('cancelrequest', 'UserController@removeFriend');
        Route::post('acceptfriend', 'UserController@acceptFriend');
        Route::post('declinefriend', 'UserController@declineFriend');
        Route::post('updateprofile', 'UserController@updateProfile')->name('updateProfile');
        Route::get('edit', 'UserController@showEditProfile')->name('editProfile');
    });
    Route::post('favorites/add/{id}', 'UserController@addFavorite');
    Route::post('favorites/remove/{id}', 'UserController@removeFavorite');
    Route::post('savedrooms/add/{room}', 'UserController@addSavedRoom');
    Route::post('savedrooms/remove/{room}', 'UserController@removeSavedRoom');
    Route::get('usersettings', 'UserController@showUserSettings')->name('userSettings');
    Route::post('updateuser', 'UserController@updateUser')->name('updateUser');

    // Rooms
    Route::group(['prefix' => 'rooms'], function(){
        Route::get('', 'RoomController@showPublicRooms')->name('publicRooms');
        Route::get('saved', 'RoomController@showSavedRooms')->name('savedRooms');
        Route::get('mine', 'RoomController@showMyRooms')->name('myRooms');
        Route::get('create', 'RoomController@showCreateRoom')->name('showCreateRoom');
        Route::post('create', 'RoomController@createRoom')->name('createRoom');
    });
    //Specific room
    Route::group(['prefix' => 'room/{room}'], function(){
        Route::get('', 'RoomController@show')->name('room');
        Route::get('syncme', 'RoomController@syncMe')->name('syncMe');
        Route::get('settings', 'RoomController@showRoomSettings')->name('roomSettings');
        Route::post('settings', 'RoomController@updateRoom')->name('updateRoom');
        Route::post('delete', 'RoomController@deleteRoom')->name('deleteRoom');
        Route::post('join', 'RoomController@join')->name('joinRoom');
        Route::post('leave', 'RoomController@leave')->name('leaveRoom');
        Route::post('addtrack', 'RoomController@addTrack');
        Route::post('removetrack', 'RoomController@removeTrack');
        Route::get('data', function(App\Room $room) {return $room;});
        Route::get('getdata', 'RoomController@getData');
    });
    Route::get('stream/{uri}', 'RoomController@getStream');

    // Track
    Route::get('track/{track}/data', function(App\Track $track) {return $track;});

});
