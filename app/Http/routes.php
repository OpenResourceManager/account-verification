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
        return view('welcome');
    });

    Route::auth();

    Route::group(['middleware' => ['admin']], function () {

        Route::get('new_user', 'AuthController@showNewUserForm');
        Route::post('new_user', 'AuthController@postNewUser');

    });

    Route::get('/dashboard', 'HomeController@index');

});
