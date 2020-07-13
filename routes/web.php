<?php
//\URL::forceSchema("https");

//Route::group(['prefix' => 'projects2'], function() {

// Authentication routes
Route::get('auth/login', 'Auth\AuthController@index')->name('login.show');
Route::post('auth/login', 'Auth\AuthController@login')->name('login.login');
Route::get('logout', 'Auth\AuthController@logout')->name('logout');

Route::post('resetgenerate', 'Auth\AuthController@generateResetLink');
Route::get('resetpassword/{token}', 'Auth\AuthController@password')->name('password.reset');
Route::post('resetpassword/{token}', 'Auth\AuthController@resetPassword')->name('password.do_reset');

Route::get('api/projects', 'ProjectController@getProjectsJSON')->name('api.projects');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/', 'HomeController@show')->name('home');

    Route::get('/projectfile/{id}', 'ProjectFileController@download')->name('projectfile.download');

    Route::group(['middleware' => ['staff']], function () {
        Route::get('/project/create', 'ProjectController@create')->name('project.create');
        Route::post('/project', 'ProjectController@store')->name('project.store');
        Route::get('/project/{id}', 'ProjectController@show')->name('project.show');
        Route::get('/project/{id}/edit', 'ProjectController@edit')->name('project.edit');
        Route::get('/project/{id}/copy', 'ProjectController@copy')->name('project.copy');
        Route::post('/project/{id}', 'ProjectController@update')->name('project.update');
        Route::get('/project/{id}/delete', 'ProjectController@destroy')->name('project.destroy');

        Route::post('project/{id}/acceptstudent', 'ProjectController@acceptStudent')->name('project.enrol');

        Route::get('/profile/{id}', 'StudentProfileController@show')->name('student.profile_show');
        Route::get('/profile/{id}/cv', 'StudentProfileController@downloadCV')->name('student.cv');
    });

    Route::group(['middleware' => ['student']], function () {
        Route::post('/choices', 'StudentChoicesController@update')->name('choices.update');

        Route::get('profile', 'StudentProfileController@edit')->name('student.profile_edit');
        Route::post('profile', 'StudentProfileController@update')->name('student.profile_update');
        Route::post('degree', 'StudentProfileController@updateDegree')->name('student.profile_update_degree');
    });


    Route::group(['middleware' => ['admin'], 'prefix' => '/admin'], function () {
        Route::get('/staff', 'StaffController@index')->name('staff.index');
        Route::post('/staff/email/{id}', 'StaffController@sendPasswordEmail');
        Route::get('/staff/import', 'StaffImportController@edit')->name('staff.import');
        Route::post('/staff/import', 'StaffImportController@update')->name('staff.do_import');

        Route::get('/students', 'StudentController@index')->name('student.index');

        Route::get('/user/create', 'UserController@create')->name('user.create');
        Route::get('/staff/create', 'StaffController@create')->name('staff.create');
        Route::get('/student/create', 'StudentController@create')->name('student.create');
        Route::post('/user', 'UserController@store')->name('user.store');
        Route::get('/user/{id}', 'UserController@show')->name('user.show');
        Route::get('/user/{id}/edit', 'UserController@edit')->name('user.edit');
        Route::post('/user/{id}', 'UserController@update')->name('user.update');
        Route::get('/user/{id}/delete', 'UserController@destroy')->name('user.destroy');
        Route::get('/user/{id}/loginas', 'UserController@logInAs')->name('user.impersonate');
        Route::post('/student/{id}/unallocate', 'StudentChoicesController@destroy')->name('student.unallocate');

        Route::get('/course', 'CourseController@index')->name('course.index');
        Route::get('/course/create', 'CourseController@create')->name('course.create');
        Route::post('/course/create', 'CourseController@store')->name('course.store');
        Route::get('/course/{id}', 'CourseController@show')->name('course.show');
        Route::get('/course/{id}/edit', 'CourseController@edit')->name('course.edit');
        Route::post('/course/{id}', 'CourseController@update')->name('course.update');
        Route::get('/course/{id}/delete', 'CourseController@destroy')->name('course.destroy');

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

        Route::get('options', 'OptionsController@edit')->name('options.edit');
        Route::post('options', 'OptionsController@update')->name('options.update');
        Route::get('options/destroy', 'OptionsController@destroy')->name('options.allocations.destroy');

        Route::get('allocations', 'ImportAllocationsController@index')->name('allocations.import');
        Route::post('allocations', 'ImportAllocationsController@update')->name('allocations.do_import');
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

        Route::get('/export/allocations', 'ExportController@allocations')->name('export.allocations');
        Route::get('/export/students', 'ExportController@allStudents')->name('export.students');
        Route::get('/export/students/single', 'ExportController@singleDegreeStudents')->name('export.students.single');
        Route::get('/export/students/dual', 'ExportController@dualDegreeStudents')->name('export.students.dual');
        Route::get('/export/staff', 'ExportController@staff')->name('export.staff');

        Route::post('site/enableapplications', 'ApplicationsController@enable')->name('admin.allow_applications');
        Route::post('site/disableapplications', 'ApplicationsController@disable')->name('admin.deny_applications');
        Route::post('site/clearunsuccessful', 'ApplicationsController@clearUnsuccessful')->name('admin.clear_unsuccessful');
        Route::get('site/clearunsuccessful', 'ApplicationsController@clearUnsuccessful')->name('admin.get_clear_unsuccessful');
    });
});
