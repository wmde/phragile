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

Route::bind('project', function($slug)
{
	return Project::where('slug', $slug)->first();
});

Route::bind('sprint', function($phabricatorID)
{
	return Sprint::where('phabricator_id', $phabricatorID)->first();
});

Route::bind('snapshot', function($snapshotID)
{
	return SprintSnapshot::where('id', $snapshotID)->first();
});

Route::get('/', [
	'as' => 'home_path',
	'uses' => 'ProjectsController@index'
]);

Route::get('/login', [
	'as' => 'login_path',
	'uses' => 'SessionsController@login'
]);

Route::get('/logout', [
	'as' => 'logout_path',
	'uses' => 'SessionsController@logout'
]);

Route::put('/conduit_certificate', [
	'middleware' => 'auth',
	'as' => 'conduit_certificate_path',
	'uses' => 'UsersController@updateCertificate'
]);

Route::get('/projects/{project}', [
	'as' => 'project_path',
	'uses' => 'ProjectsController@show'
]);

Route::post('projects/store', [
	'middleware' => 'auth',
	'as' => 'create_project_path',
	'uses' => 'ProjectsController@store'
]);

Route::get('/projects/{project}/sprints/create', [
	'as' => 'create_sprint_path',
	'uses' => 'SprintsController@create'
]);

Route::post('projects/{project}/sprints/store', [
	'middleware' => 'auth',
	'as' => 'store_sprint_path',
	'uses' => 'SprintsController@store'
]);

Route::get('/sprints/{sprint}', [
	'as' => 'sprint_path',
	'uses' => 'SprintsController@show'
]);

Route::get('/sprints/{sprint}/snapshot', [ // should technically be a POST
	'as' => 'create_snapshot_path',
	'middleware' => 'auth',
	'uses' => 'SprintSnapshotsController@store'
]);

Route::get('/snapshots/{snapshot}/delete', [ // should be a DELETE
	'as' => 'delete_snapshot_path',
	'middleware' => 'auth',
	'uses' => 'SprintSnapshotsController@delete'
]);

Route::get('/live/{sprint}', [
	'as' => 'sprint_live_path',
	'uses' => 'SprintsController@showWithLiveData'
]);

Route::get('snapshots/{snapshot}', [
	'as' => 'snapshot_path',
	'uses' => 'SprintSnapshotsController@show'
]);
