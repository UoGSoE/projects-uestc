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
    Route::post('/user/chooseprojects', 'UserController@chooseProjects');
    Route::get('/user', 'UserController@index');
    Route::get('/user/create', 'UserController@create');
    Route::post('/user/create', 'UserController@store');
    Route::get('/user/{id}', 'UserController@show');
    Route::get('/user/{id}/edit', 'UserController@edit');
    Route::post('/user/{id}/edit', 'UserController@update');
    Route::delete('/user/{id}', 'UserController@destroy');

    // Course routes
    Route::get('/course', 'CourseController@index');
    Route::get('/course/create', 'CourseController@create');
    Route::post('/course/create', 'CourseController@store');
    Route::get('/course/{id}', 'CourseController@show');
    Route::get('/course/{id}/edit', 'CourseController@edit');
    Route::post('/course/{id}/edit', 'CourseController@update');
    Route::delete('/course/{id}', 'CourseController@destroy');
    Route::get('/course/{id}/editstudents', 'CourseController@editStudents');
    Route::patch('/course/{id}/editstudents', 'CourseController@updateStudents');

    // Project routes
    Route::post('project/{id}/acceptstudent', 'ProjectController@acceptStudents');
    Route::get('project/{id}/copy', 'ProjectController@duplicate');
    Route::resource('project', 'ProjectController');

    // Permission routes
    Route::resource('permission', 'PermissionController');

    // Role routes
    Route::resource('role', 'RoleController');

    // Project Type routes
    Route::resource('projecttype', 'ProjectTypeController');

    // Location routes
    Route::resource('location', 'LocationController');

    // Programme routes
    Route::resource('programme', 'ProgrammeController');

    // Report routes
    Route::get('/report/projects', 'ReportController@allProjects');
    Route::get('/report/students', 'ReportController@allStudents');
});

// Define our authentication middleware controller
Route::controllers([
    'auth' => 'Auth\AuthController',
]);
