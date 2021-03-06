<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    abort(503);
});

$app->post('register', 'AuthController@register');
$app->post('login', 'AuthController@login');

$app->group(['middleware' => 'auth'], function () use ($app) {
    $app->get('logout', 'AuthController@logout');

    $app->get('me', 'MeController@index');

    $app->get('posts', 'PostsController@index');
    $app->post('posts', 'PostsController@store');
    $app->put('posts/{id}', 'PostsController@update');
    $app->delete('posts/{id}', 'PostsController@destroy');

    $app->post('posts/{id}/reply', 'ReplyController@store');
});
