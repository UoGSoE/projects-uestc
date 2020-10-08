<?php

//\URL::forceSchema("https");

//Route::group(['prefix' => 'projects2'], function() {

// Authentication routes
Route::get('auth/login', [App\Http\Controllers\Auth\AuthController::class, 'index'])->name('login.show');
Route::post('auth/login', [App\Http\Controllers\Auth\AuthController::class, 'login'])->name('login.login');
Route::get('logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');

Route::post('resetgenerate', [App\Http\Controllers\Auth\AuthController::class, 'generateResetLink']);
Route::get('resetpassword/{token}', [App\Http\Controllers\Auth\AuthController::class, 'password'])->name('password.reset');
Route::post('resetpassword/{token}', [App\Http\Controllers\Auth\AuthController::class, 'resetPassword'])->name('password.do_reset');

Route::get('api/projects', [App\Http\Controllers\ProjectController::class, 'getProjectsJSON'])->name('api.projects');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'show'])->name('home');

    Route::get('/projectfile/{id}', [App\Http\Controllers\ProjectFileController::class, 'download'])->name('projectfile.download');

    Route::group(['middleware' => ['staff']], function () {
        Route::get('/project/create', [App\Http\Controllers\ProjectController::class, 'create'])->name('project.create');
        Route::post('/project', [App\Http\Controllers\ProjectController::class, 'store'])->name('project.store');
        Route::get('/project/{id}', [App\Http\Controllers\ProjectController::class, 'show'])->name('project.show');
        Route::get('/project/{id}/edit', [App\Http\Controllers\ProjectController::class, 'edit'])->name('project.edit');
        Route::get('/project/{id}/copy', [App\Http\Controllers\ProjectController::class, 'copy'])->name('project.copy');
        Route::post('/project/{id}', [App\Http\Controllers\ProjectController::class, 'update'])->name('project.update');
        Route::get('/project/{id}/delete', [App\Http\Controllers\ProjectController::class, 'destroy'])->name('project.destroy');

        Route::post('project/{id}/acceptstudent', [App\Http\Controllers\ProjectController::class, 'acceptStudent'])->name('project.enrol');

        Route::get('/profile/{id}', [App\Http\Controllers\StudentProfileController::class, 'show'])->name('student.profile_show');
        Route::get('/profile/{id}/cv', [App\Http\Controllers\StudentProfileController::class, 'downloadCV'])->name('student.cv');
    });

    Route::group(['middleware' => ['student']], function () {
        Route::post('/choices', [App\Http\Controllers\StudentChoicesController::class, 'update'])->name('choices.update');

        Route::get('profile', [App\Http\Controllers\StudentProfileController::class, 'edit'])->name('student.profile_edit');
        Route::post('profile', [App\Http\Controllers\StudentProfileController::class, 'update'])->name('student.profile_update');
        Route::post('degree', [App\Http\Controllers\StudentProfileController::class, 'updateDegree'])->name('student.profile_update_degree');
    });

    Route::group(['middleware' => ['admin'], 'prefix' => '/admin'], function () {
        Route::get('/staff', [App\Http\Controllers\StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff/email/{id}', [App\Http\Controllers\StaffController::class, 'sendPasswordEmail']);
        Route::get('/staff/import', [App\Http\Controllers\StaffImportController::class, 'edit'])->name('staff.import');
        Route::post('/staff/import', [App\Http\Controllers\StaffImportController::class, 'update'])->name('staff.do_import');

        Route::get('/students', [App\Http\Controllers\StudentController::class, 'index'])->name('student.index');

        Route::get('/user/create', [App\Http\Controllers\UserController::class, 'create'])->name('user.create');
        Route::get('/staff/create', [App\Http\Controllers\StaffController::class, 'create'])->name('staff.create');
        Route::get('/student/create', [App\Http\Controllers\StudentController::class, 'create'])->name('student.create');
        Route::post('/user', [App\Http\Controllers\UserController::class, 'store'])->name('user.store');
        Route::get('/user/{id}', [App\Http\Controllers\UserController::class, 'show'])->name('user.show');
        Route::get('/user/{id}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('user.edit');
        Route::post('/user/{id}', [App\Http\Controllers\UserController::class, 'update'])->name('user.update');
        Route::get('/user/{id}/delete', [App\Http\Controllers\UserController::class, 'destroy'])->name('user.destroy');
        Route::get('/user/{id}/loginas', [App\Http\Controllers\UserController::class, 'logInAs'])->name('user.impersonate');
        Route::post('/student/{id}/unallocate', [App\Http\Controllers\StudentChoicesController::class, 'destroy'])->name('student.unallocate');

        Route::get('/course', [App\Http\Controllers\CourseController::class, 'index'])->name('course.index');
        Route::get('/course/create', [App\Http\Controllers\CourseController::class, 'create'])->name('course.create');
        Route::post('/course/create', [App\Http\Controllers\CourseController::class, 'store'])->name('course.store');
        Route::get('/course/{id}', [App\Http\Controllers\CourseController::class, 'show'])->name('course.show');
        Route::get('/course/{id}/edit', [App\Http\Controllers\CourseController::class, 'edit'])->name('course.edit');
        Route::post('/course/{id}', [App\Http\Controllers\CourseController::class, 'update'])->name('course.update');
        Route::get('/course/{id}/delete', [App\Http\Controllers\CourseController::class, 'destroy'])->name('course.destroy');

        Route::get('/course/{id}/students', [App\Http\Controllers\CourseEnrolmentController::class, 'edit'])->name('enrol.edit');
        Route::post('/course/{id}/students', [App\Http\Controllers\CourseEnrolmentController::class, 'update'])->name('enrol.update');
        Route::post('/course/{id}/removestudents', [App\Http\Controllers\CourseEnrolmentController::class, 'destroy'])->name('enrol.destroy');
        Route::get('/course/{id}/removestudents', [App\Http\Controllers\CourseEnrolmentController::class, 'destroy'])->name('enrol.get_destroy'); // need to fix bootstrap model JS in layout...

        Route::get('discipline', [App\Http\Controllers\DisciplineController::class, 'index'])->name('discipline.index');
        Route::get('discipline/create', [App\Http\Controllers\DisciplineController::class, 'create'])->name('discipline.create');
        Route::post('discipline', [App\Http\Controllers\DisciplineController::class, 'store'])->name('discipline.store');
        Route::get('discipline/{id}/edit', [App\Http\Controllers\DisciplineController::class, 'edit'])->name('discipline.edit');
        Route::post('discipline/{id}', [App\Http\Controllers\DisciplineController::class, 'update'])->name('discipline.update');

        Route::get('events', [App\Http\Controllers\EventLogController::class, 'index'])->name('event.index');

        Route::get('options', [App\Http\Controllers\OptionsController::class, 'edit'])->name('options.edit');
        Route::post('options', [App\Http\Controllers\OptionsController::class, 'update'])->name('options.update');
        Route::get('options/destroy', [App\Http\Controllers\OptionsController::class, 'destroy'])->name('options.allocations.destroy');

        Route::get('allocations', [App\Http\Controllers\ImportAllocationsController::class, 'index'])->name('allocations.import');
        Route::post('allocations', [App\Http\Controllers\ImportAllocationsController::class, 'update'])->name('allocations.do_import');
    });

    Route::group(['middleware' => ['convenor_or_admin'], 'prefix' => '/admin'], function () {
        Route::get('/report/projects/bytype/{id}', [App\Http\Controllers\ReportController::class, 'allProjectsOfDiscipline'])->name('report.projects_of_discipline');
        Route::get('/report/projects/bylocation/{id}', [App\Http\Controllers\ReportController::class, 'allProjectsAtLocation']);
        Route::get('/report/projects', [App\Http\Controllers\ReportController::class, 'allProjects'])->name('report.projects');
        Route::get('/report/students', [App\Http\Controllers\ReportController::class, 'allStudents'])->name('report.students');
        Route::get('/report/staff', [App\Http\Controllers\ReportController::class, 'allStaff'])->name('report.staff');

        Route::get('/bulkallocate', [App\Http\Controllers\BulkAllocateController::class, 'edit'])->name('bulkallocate.edit');
        Route::post('/bulkallocate', [App\Http\Controllers\BulkAllocateController::class, 'update'])->name('bulkallocate.update');

        Route::get('/bulkactive', [App\Http\Controllers\BulkActiveController::class, 'edit'])->name('bulkactive.edit');
        Route::post('/bulkactive', [App\Http\Controllers\BulkActiveController::class, 'update'])->name('bulkactive.update');

        Route::get('/export/allocations', [App\Http\Controllers\ExportController::class, 'allocations'])->name('export.allocations');
        Route::get('/export/students', [App\Http\Controllers\ExportController::class, 'allStudents'])->name('export.students');
        Route::get('/export/students/single', [App\Http\Controllers\ExportController::class, 'singleDegreeStudents'])->name('export.students.single');
        Route::get('/export/students/dual', [App\Http\Controllers\ExportController::class, 'dualDegreeStudents'])->name('export.students.dual');
        Route::get('/export/staff', [App\Http\Controllers\ExportController::class, 'staff'])->name('export.staff');

        Route::post('site/enableapplications', [App\Http\Controllers\ApplicationsController::class, 'enable'])->name('admin.allow_applications');
        Route::post('site/disableapplications', [App\Http\Controllers\ApplicationsController::class, 'disable'])->name('admin.deny_applications');
        Route::post('site/clearunsuccessful', [App\Http\Controllers\ApplicationsController::class, 'clearUnsuccessful'])->name('admin.clear_unsuccessful');
        Route::get('site/clearunsuccessful', [App\Http\Controllers\ApplicationsController::class, 'clearUnsuccessful'])->name('admin.get_clear_unsuccessful');
    });
});
