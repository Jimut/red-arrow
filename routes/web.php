<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'HomeController@index');

Route::get('auth/activate/{token}', [
    'as' => 'user.activate',
    'uses' => 'Auth\ActivationController@activate'
]);
Route::get('auth/{provider}', 'Auth\SocialAuthController@redirect');
Route::get('auth/{provider}/callback', 'Auth\SocialAuthController@callback');
Auth::routes();

Route::resource('donor', 'DonorController');
Route::resource('hospital', 'HospitalController');

Route::get('find', [
    'as' => 'donor.find',
    'uses' => 'DonorController@find'
]);
Route::get('notification', [
    'as' => 'donor.notification',
    'uses' => 'DonorController@showNotification'
]);

Route::post('appointment', [
    'as' => 'appointment.store',
    'uses' => 'AppointmentController@store'
]);
