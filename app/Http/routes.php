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

// Redirect to the main page
Route::get('/', function () {
    return redirect()->route('home');
});
// Redirect to the main page
Route::get('/home', function () {
    return redirect()->route('home');
});
// Enable the auth routes (login, logout, ect..)
Route::auth();
// A user's profile page
Route::get('profile', ['as' => 'profile', 'uses' => 'HomeController@profile']);
// Save any changes made to a user's profile
Route::post('profile', ['as' => 'profile', 'uses' => 'HomeController@saveProfile']);
//
Route::get('passwd/change', ['as' => 'change_passwd', 'uses' => 'HomeController@changePassword']);
// Routes that only administrators can visit.
Route::group(['middleware' => ['admin']], function () {
    // Analytic routes
    Route::get('dashboard', ['as' => 'dashboard', 'uses' => 'HomeController@dashboard']);
    Route::get('timeline', ['as' => 'dashboard', 'uses' => 'HomeController@timeline']);
    // Preferences
    Route::get('preferences', ['as' => 'preferences', 'uses' => 'HomeController@getPreferences']);
    Route::post('preferences', ['as' => 'preferences', 'uses' => 'HomeController@savePreferences']);
    // Local user management routes
    Route::group(['prefix' => 'users'], function () {
        // View all users
        Route::get('/', ['as' => 'users', 'uses' => 'HomeController@userIndex']);
        // User creation form
        Route::get('/new', ['as' => 'newuser', 'uses' => 'HomeController@showNewUserForm']);
        // Post user creation form
        Route::post('/new', ['as' => 'newuser', 'uses' => 'HomeController@postNewUser']);
        // All routes that pertain to a specific user
        Route::group(['prefix' => '{id}'], function () {
            // View a user's info
            Route::get('/', 'HomeController@getUser');
            // Save any chnages made to a user's info
            Route::post('/save', 'HomeController@saveUser');
            // Delete a user
            Route::get('/del', 'HomeController@deleteUser');
            // View a user in the trash
            Route::get('/trash', 'HomeController@viewTrashedUser');
            // Restore the user from the trash
            Route::post('/trash', 'HomeController@restoreTrashedUser');
        });
    });
});
// Routes that deal with verifying external users
Route::group(['prefix' => 'verify'], function () {
    // The main page
    Route::get('/', ['as' => 'home', 'uses' => 'VerificationController@index']);
    // Send verification form
    Route::post('/', ['as' => 'home', 'uses' => 'VerificationController@verify']);
    // The verification request was successful, show the success page
    Route::get('/success', ['as' => 'verify_success', 'uses' => 'VerificationController@verifySuccess']);
    // The verification request was unsuccessful, show the failure page
    Route::get('/failure', ['as' => 'verify_fail', 'uses' => 'VerificationController@verifyFail']);
});
