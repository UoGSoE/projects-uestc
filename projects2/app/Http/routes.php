<?php

// Authentication routes
Route::get('/auth/login', function () {
    $data['page_title'] = "Log In";
    return View::make('login_form', $data);
});
Route::post('/auth/login', 'Auth\AuthController@login');
Route::get('/logout', 'Auth\AuthController@logout');

Route::get('/resetpassword/{token}', 'Auth\AuthController@password');
Route::post('/resetpassword/{token}', 'Auth\AuthController@resetPassword');

// Routes you can only get to once authenticated
Route::group(['middleware' => ['auth']], function () {

    // Homepage
    Route::get('/', function () {
        return view('home');
    });

    // User routes
    Route::get('/user/{id}/loginas', 'UserController@logInAs');
    Route::post('/user/chooseprojects', 'UserController@chooseProjects');
    Route::get('/user', 'UserController@index');
    Route::get('/user/students', 'UserController@indexStudents');
    Route::get('/user/staff', 'UserController@indexStaff');
    Route::get('/user/create', 'UserController@create');
    Route::post('/user/create', 'UserController@store');
    Route::get('/user/{id}', 'UserController@show');
    Route::get('/user/{id}/edit', 'UserController@edit');
    Route::post('/user/{id}/edit', 'UserController@update');
    Route::delete('/user/{id}', 'UserController@destroy');
    Route::get('/user/{id}/delete', 'UserController@destroy');

    // Course routes
    Route::get('/course', 'CourseController@index');
    Route::get('/course/create', 'CourseController@create');
    Route::post('/course/create', 'CourseController@store');
    Route::get('/course/{id}', 'CourseController@show');
    Route::get('/course/{id}/edit', 'CourseController@edit');
    Route::post('/course/{id}/edit', 'CourseController@update');
    Route::delete('/course/{id}', 'CourseController@destroy');
    Route::get('course/{id}/delete', 'CourseController@destroy');
    Route::get('/course/{id}/editstudents', 'CourseController@editStudents');
    Route::patch('/course/{id}/editstudents', 'CourseController@updateStudents');
    Route::get('/course/{id}/removestudents', 'CourseController@removeStudents');

    // Project routes
    Route::post('project/{id}/acceptstudent', 'ProjectController@acceptStudents');
    Route::get('project/{id}/copy', 'ProjectController@duplicate');
    Route::resource('project', 'ProjectController');
    Route::get('project/{id}/delete', 'ProjectController@destroy');

    // Permission routes
    Route::resource('permission', 'PermissionController');

    // Role routes
    Route::resource('role', 'RoleController');

    // Project Type routes
    Route::resource('projecttype', 'ProjectTypeController');
    Route::get('projecttype/{id}/delete', 'ProjectTypeController@destroy');

    // Location routes
    Route::resource('location', 'LocationController');
    Route::get('location/{id}/delete', 'LocationController@destroy');

    // Programme routes
    Route::resource('programme', 'ProgrammeController');
    Route::get('programme/{id}/delete', 'ProgrammeController@destroy');

    // Report routes
    Route::get('/report/projects/bytype/{id}', 'ReportController@allProjectsOfType');
    Route::get('/report/projects/bylocation/{id}', 'ReportController@allProjectsAtLocation');
    Route::get('/report/projects', 'ReportController@allProjects');
    Route::get('/report/students', 'ReportController@allStudents');
    Route::get('/report/staff', 'ReportController@allStaff');

    // Event routes
    Route::get('events', 'EventLogController@index');
});

// Define our authentication middleware controller
Route::controllers([
    'auth' => 'Auth\AuthController',
]);
