<?php

Route::post('users', 'API\UserController@store');
Route::post('users/login', 'API\UserController@login');
Route::get('user', 'API\UserController@user');
Route::post('user/logout', 'API\UserController@logout');

Route::resource('medias', 'API\MediaController');