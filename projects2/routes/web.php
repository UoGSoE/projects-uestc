<?php
//\URL::forceSchema("https");

//Route::group(['prefix' => 'projects2'], function() {

// Authentication routes
Route::get('auth/login', function () {
    $data['page_title'] = "Log In";
    return View::make('login_form', $data);
});
Route::post('auth/login', 'Auth\AuthController@login');
Route::get('logout', 'Auth\AuthController@logout');

Route::post('resetgenerate', 'Auth\AuthController@generateResetLink');
Route::get('resetpassword/{token}', 'Auth\AuthController@password')->name('password.reset');
Route::post('resetpassword/{token}', 'Auth\AuthController@resetPassword');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/', 'HomeController@show');

    Route::get('/projectfile/{id}', 'ProjectFileController@download')->name('projectfile.download');

    Route::group(['middleware' => ['staff']], function () {
        Route::get('/project/create', 'ProjectController@create')->name('project.create');
        Route::post('/project', 'ProjectController@store')->name('project.store');
        Route::get('/project/{id}', 'ProjectController@show')->name('project.show');
        Route::get('/project/{id}/edit', 'ProjectController@edit')->name('project.edit');
        Route::get('/project/{id}/copy', 'ProjectController@copy')->name('project.copy');
        Route::post('/project/{id}', 'ProjectController@update')->name('project.update');
        Route::delete('/project/{id}', 'ProjectController@destroy')->name('project.destroy');

        Route::post('project/{id}/acceptstudent', 'ProjectController@acceptStudents')->name('project.enrol');

        Route::get('/profile/{id}', 'StudentProfileController@show')->name('student.profile_show');
        Route::get('/profile/{id}/cv', 'StudentProfileController@downloadCV')->name('student.cv');

    });

    Route::group(['middleware' => ['student']], function () {
        Route::post('/choices', 'StudentChoicesController@update')->name('choices.update');

        Route::get('profile', 'StudentProfileController@edit')->name('student.profile_edit');
        Route::post('profile', 'StudentProfileController@update')->name('student.profile_update');
    });


    Route::group(['middleware' => ['admin'], 'prefix' => '/admin'], function () {
        Route::get('/staff', 'StaffController@index')->name('staff.index');
        Route::get('/staff/import', 'StaffImportController@edit')->name('staff.import');
        Route::post('/staff/import', 'StaffImportController@update')->name('staff.do_import');

        Route::get('/students', 'StudentController@index')->name('student.index');

        Route::get('/user/create', 'UserController@create')->name('user.create');
        Route::post('/user', 'UserController@store')->name('user.store');
        Route::get('/user/{id}', 'UserController@show')->name('user.show');
        Route::get('/user/{id}/edit', 'UserController@edit')->name('user.edit');
        Route::post('/user/{id}', 'UserController@update')->name('user.update');
        Route::delete('/user/{id}', 'UserController@destroy')->name('user.destroy');
        Route::get('/user/{id}/loginas', 'UserController@logInAs')->name('user.impersonate');

        Route::get('/course', 'CourseController@index')->name('course.index');
        Route::get('/course/create', 'CourseController@create')->name('course.create');
        Route::post('/course/create', 'CourseController@store')->name('course.store');
        Route::get('/course/{id}', 'CourseController@show')->name('course.show');
        Route::get('/course/{id}/edit', 'CourseController@edit')->name('course.edit');
        Route::post('/course/{id}', 'CourseController@update')->name('course.update');
        Route::delete('/course/{id}', 'CourseController@destroy')->name('course.destroy');

        Route::get('/course/{id}/students', 'CourseEnrolmentController@edit')->name('enrol.edit');
        Route::post('/course/{id}/students', 'CourseEnrolmentController@update')->name('enrol.update');
        Route::post('/course/{id}/removestudents', 'CourseEnrolmentController@destroy')->name('enrol.destroy');
        Route::get('/course/{id}/removestudents', 'CourseEnrolmentController@destroy')->name('enrol.get_destroy'); // need to fix bootstrap model JS in layout...

        Route::get('discipline', 'DisciplineController@index')->name('discipline.index');
        Route::get('discipline/create', 'DisciplineController@create')->name('discipline.create');
        Route::post('discipline', 'DisciplineController@store')->name('discipline.store');
        Route::get('discipline/{id}/edit', 'DisciplineController@edit')->name('discipline.edit');
        Route::post('discipline/{id}', 'DisciplineController@update')->name('discipline.update');

        Route::get('events', 'EventLogController@index')->name('event.index');
    });

    Route::group(['middleware' => ['convenor_or_admin'], 'prefix' => '/admin'], function () {

        Route::get('/report/projects/bytype/{id}', 'ReportController@allProjectsOfDiscipline');
        Route::get('/report/projects/bylocation/{id}', 'ReportController@allProjectsAtLocation');
        Route::get('/report/projects', 'ReportController@allProjects')->name('report.projects');
        Route::get('/report/students', 'ReportController@allStudents')->name('report.students');
        Route::get('/report/staff', 'ReportController@allStaff')->name('report.staff');

        Route::get('/bulkallocate', 'BulkAllocateController@edit')->name('bulkallocate.edit');
        Route::post('/bulkallocate', 'BulkAllocateController@update')->name('bulkallocate.update');

        Route::get('/bulkactive', 'BulkActiveController@edit')->name('bulkactive.edit');
        Route::post('/bulkactive', 'BulkActiveController@update')->name('bulkactive.update');

        Route::post('site/enableapplications', 'ApplicationsController@enable')->name('admin.allow_applications');
        Route::post('site/disableapplications', 'ApplicationsController@disable')->name('admin.deny_applications');
        Route::post('site/clearunsuccessful', 'ApplicationsController@clearUnsuccessful')->name('admin.clear_unsuccessful');
    });
});
