<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/profile', 'AuthController@me');

//stuff
$router->get('/stuffs', 'StuffController@index');

$router->group(['prefix' => 'stuff', 'middleware' => 'auth'], function() use ($router){
    // static routes
    $router->get('/data', 'StuffController@index');
    $router->post('/', 'StuffController@store');
    $router->get('/trash', 'StuffController@trash');
    

    // dynamic routes
    $router->get('{id}', 'StuffController@show');
    $router->patch('/{id}', 'StuffController@update');
    $router->delete('/{id}', 'StuffController@destroy');
    $router->get('/restore/{id}', 'StuffController@restore');
    $router->delete('/permanent/{id}', 'StuffController@deletePermanent');
});

$router->group(['prefix' => 'user'], function() use ($router){
    // static routes
    $router->get('/data', 'UserController@index');
    $router->post('/', 'UserController@store');
    $router->get('/trash', 'UserController@trash');
    

    // dynamic routes
    $router->get('{id}', 'UserController@show');
    $router->patch('/{id}', 'UserController@update');
    $router->delete('/{id}', 'UserController@destroy');
    $router->get('/restore/{id}', 'UserController@restore');
    $router->delete('/permanent/{id}', 'UserController@deletePermanent');
});

$router->group(['prefix' => 'stuff-stock', 'middleware' => 'auth'], function() use ($router){
    // static routes
    $router->get('/data', 'StuffStockController@index');
    $router->post('/', 'StuffStockController@store');
    $router->get('/trash', 'StuffStockController@trash');
    $router->post('add-stock/{id}', 'StuffStockController@addStock');

    // dynamic routes
    $router->get('detail/{id}', 'StuffStockController@show');
    $router->patch('update/{id}', 'StuffStockController@update');
    $router->delete('delete/{id}', 'StuffStockController@destroy');
    $router->get('/restore/{id}', 'StuffStockController@restore');
    $router->delete('/permanent/{id}', 'StuffStockController@deletePermanent');
});

$router->group(['prefix' => 'restoration', 'middleware' => 'auth'], function() use ($router){
    // static routes
    $router->get('/data', 'RestorationController@index');
    $router->post('/store', 'RestorationController@store');
    $router->get('/trash', 'RestorationController@trash');
    

    // dynamic routes
    $router->get('{id}', 'RestorationController@show');
    $router->patch('/{id}', 'RestorationController@update');
    $router->delete('/{id}', 'RestorationController@destroy');
    $router->get('/restore/{id}', 'RestorationController@restore');
    $router->delete('/permanent/{id}', 'RestorationController@deletePermanent');
});

$router->group(['prefix' => 'lending', 'middleware' => 'auth'], function() use ($router){
    // static routes
    $router->get('/data', 'LendingController@index');
    $router->post('/store', 'LendingController@store');
    $router->get('/trash', 'LendingController@trash');
    

    // dynamic routes
    $router->get('detail/{id}', 'LendingController@show');
    $router->patch('update/{id}', 'LendingController@update');
    $router->delete('delete/{id}', 'LendingController@destroy');
    $router->get('/restore/{id}', 'LendingController@restore');
    $router->delete('/permanent/{id}', 'LendingController@deletePermanent');
});

$router->group(['prefix' => 'inbound-stuff', 'middleware' => 'auth'], function() use ($router){
    // static routes
    $router->get('/data', 'InboundStuffController@index');
    $router->post('/store', 'InboundStuffController@store');
    $router->get('/trash', 'InboundStuffController@trash');
    

    // dynamic routes
    $router->get('detail/{id}', 'InboundStuffController@show');
    $router->patch('update/{id}', 'InboundStuffController@update');
    $router->delete('delete/{id}', 'InboundStuffController@destroy');
    $router->get('/restore/{id}', 'InboundStuffController@restore');
    $router->delete('/permanent/{id}', 'InboundStuffController@deletePermanent');
});