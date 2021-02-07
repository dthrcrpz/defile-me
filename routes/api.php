<?php

Route::group(['middleware' => ['guest']], function () {
	Route::post('users/login', 'API\UserController@login');
	Route::post('users', 'API\UserController@store');
});

Route::group(['middleware' => ['custom.auth']], function () {
	Route::get('user', 'API\UserController@user');
	Route::post('user/logout', 'API\UserController@logout');
	
	Route::resource('medias', 'API\MediaController');
});