<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/ping', function() {
    return ['pong' => true];
});

Route::get('/401', 'App\Http\Controllers\AuthController@unauthorized')->name('login');

Route::post('/auth/login', 'App\Http\Controllers\AuthController@login');
Route::post('/auth/logout', 'App\Http\Controllers\AuthController@logout');
// Route::post('/auth/refresh', 'AuthController@refresh');

Route::post('/user', 'App\Http\Controllers\AuthController@create');
// Route::put('/user', 'UserController@update');
// Route::post('/user/avatar/', 'UserController@updateAvatar');
// Route::post('/user/cover/', 'UserController@updateCover');

// Route::get('/feed', 'FeedController@read');
// Route::get('/user/feed', 'FeedController@userFeed');
// Route::get('/user/{id}/feed', 'FeedController@userFeed');

// Route::get('/user', 'UserController@read');
// Route::get('/user/{id}', 'UserController@read');

// Route::post('/feed', 'FeedController@create');

// Route::post('/post/{id}/like', 'PostController@like');
// Route::post('/post/{id}/comment', 'PostController@comment');

// Route::get('/search', 'SearchController@search');
