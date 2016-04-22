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

    Route::get('/home', function () {
        return redirect()->route('home');
    });

    Route::auth();

    Route::group(['middleware' => ['admin'], 'prefix' => 'users'], function () {

        Route::get('/', ['as' => 'users', 'uses' => 'HomeController@userIndex']);

        Route::get('/new', ['as' => 'newuser', 'uses' => 'HomeController@showNewUserForm']);
        Route::post('/new', ['as' => 'newuser', 'uses' => 'HomeController@postNewUser']);

        Route::group(['prefix' => '{id}'], function () {
            Route::get('/', 'HomeController@getUser');
            Route::get('/del', 'HomeController@deleteUser');
            Route::get('/trash', 'HomeController@viewTrashedUser');
            Route::post('/trash', 'HomeController@restoreTrashedUser');
            Route::post('/save', 'HomeController@saveUser');
        });

    });

    Route::group(['prefix' => 'verify'], function () {
        Route::get('/', ['as' => 'home', 'uses' => 'VerificationController@index']);
        Route::post('/', ['as' => 'home', 'uses' => 'VerificationController@verify']);
        Route::get('/failure', ['as' => 'verify_fail', 'uses' => 'VerificationController@verifyFail']);
        Route::get('/success', ['as' => 'verify_success', 'uses' => 'VerificationController@verifySuccess']);
    });

    Route::get('dashboard', ['as' => 'dashboard', 'uses' => 'HomeController@dashboard']);

    Route::get('profile', ['as' => 'profile', 'uses' => 'HomeController@profile']);

    Route::post('profile', ['as' => 'profile', 'uses' => 'HomeController@saveProfile']);

    Route::get('passwd/change', ['as' => 'change_passwd', 'uses' => 'HomeController@changePassword']);

});
