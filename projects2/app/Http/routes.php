<?php

// Authentication routes
Route::get('/auth/login', function () {
    $data['page_title'] = "Log In";
    return View::make('login_form', $data);
});
Route::post('/auth/login', 'Auth\AuthController@login');
Route::get('/logout', 'Auth\AuthController@logout');

// Routes you can only get to once authenticated
Route::group(['middleware' => ['auth']], function () {

    // Homepage
    Route::get('/', function () {
        return view('home');
    });

    // User routes
    Route::get('/user', 'UserController@index');
    Route::get('/user/{id}', 'UserController@show');
    Route::get('/user/{id}/edit', 'UserController@edit');
    Route::post('/user/{id}/edit', 'UserController@update');
});

// Define our authentication middleware controller
Route::controllers([
    'auth' => 'Auth\AuthController',
]);
