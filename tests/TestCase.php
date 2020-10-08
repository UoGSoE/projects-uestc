<?php

namespace Tests;

use App\Exceptions\Handler;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable foreign key support for SQLITE databases
        if (DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
            DB::statement(DB::raw('PRAGMA foreign_keys=on'));
        }
    }

    public function fromUrl($url = '')
    {
        $this->app['session']->setPreviousUrl($url);

        return $this;
    }

    protected function createStudent($attribs = [])
    {
        return \App\User::factory()->student()->create($attribs);
    }

    protected function createStaff($attribs = [])
    {
        return \App\User::factory()->staff()->create($attribs);
    }

    protected function createProject($attribs = [])
    {
        return \App\Project::factory()->create($attribs);
    }

    protected function createAdmin($attribs = [])
    {
        return \App\User::factory()->admin()->create($attribs);
    }

    protected function createConvenor($attribs = [])
    {
        return \App\User::factory()->convenor()->create($attribs);
    }

    protected function createDiscipline($attribs = [])
    {
        return \App\Discipline::factory()->create($attribs);
    }

    protected function createCourse($attribs = [])
    {
        return \App\Course::factory()->create($attribs);
    }

    protected function createToken($attribs = [])
    {
        return \App\PasswordReset::factory()->create($attribs);
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct()
            {
            }

            public function report(Throwable $e)
            {
                // no-op
            }

            public function render($request, Throwable $e)
            {
                throw $e;
            }
        });
    }
}
