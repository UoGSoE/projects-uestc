<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Exception;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    public function fromUrl($url = '')
    {
        $this->app['session']->setPreviousUrl($url);
        return $this;
    }

    protected function createStudent($attribs = [])
    {
        return factory(\App\User::class)->states('student')->create($attribs);
    }

    protected function createStaff($attribs = [])
    {
        return factory(\App\User::class)->states('staff')->create($attribs);
    }

    protected function createProject($attribs = [])
    {
        return factory(\App\Project::class)->create($attribs);
    }

    protected function createAdmin($attribs = [])
    {
        return factory(\App\User::class)->states('admin')->create($attribs);
    }

    protected function createConvenor($attribs = [])
    {
        return factory(\App\User::class)->states('convenor')->create($attribs);
    }

    protected function createDiscipline($attribs = [])
    {
        return factory(\App\Discipline::class)->create($attribs);
    }

    protected function createCourse($attribs = [])
    {
        return factory(\App\Course::class)->create($attribs);
    }

    protected function createToken($attribs = [])
    {
        return factory(\App\PasswordReset::class)->create($attribs);
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}

            public function report(Exception $e)
            {
                // no-op
            }

            public function render($request, Exception $e) {
                throw $e;
            }
        });
    }
}
