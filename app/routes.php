<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('index');
});

Route::get('/login', [
	'as' => 'login_path',
	'uses' => 'SessionsController@login'
]);

Route::get('/logout', [
	'as' => 'logout_path',
	'uses' => 'SessionsController@logout'
]);

Route::put('/conduit_certificate', [
	'before' => 'auth',
	'as' => 'conduit_certificate_path',
	'uses' => 'UsersController@updateCertificate'
]);

Route::get('/projects/{slug}', [
	'as' => 'project_path',
	'uses' => 'ProjectsController@show'
]);
