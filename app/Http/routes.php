<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function () {

    Route::get('/', function () {
        return redirect()->route('home');
    });

    Route::auth();

    Route::group(['middleware' => ['admin']], function () {
        Route::get('users', ['as' => 'users', 'uses' => 'HomeController@userIndex']);
        Route::get('users/{id}', 'HomeController@getUser');
        Route::post('users/{id}', 'HomeController@saveUser');
        Route::get('users/new', ['as' => 'newuser', 'uses' => 'HomeController@showNewUserForm']);
        Route::post('users/new', ['as' => 'newuser', 'uses' => 'HomeController@postNewUser']);
    });

    Route::get('home', ['as' => 'home', 'uses' => 'HomeController@index']);

    Route::get('dashboard', ['as' => 'dashboard', 'uses' => 'HomeController@dashboard']);

    Route::get('profile', ['as' => 'profile', 'uses' => 'HomeController@profile']);

    Route::post('profile', ['as' => 'profile', 'uses' => 'HomeController@saveProfile']);

});


