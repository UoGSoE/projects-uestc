<?php

namespace Tests;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseMigrations;

    /**
     * Prepare for Dusk test execution.
     *
     * @beforeClass
     * @return void
     */
    public static function prepare()
    {
        static::startChromeDriver();
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected function driver()
    {
        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()
        );
    }

    protected function createProjectFile($attribs)
    {
        return factory(\App\ProjectFile::class)->create($attribs);
    }

    protected function createProjectLink($attribs)
    {
        return factory(\App\ProjectLink::class)->create($attribs);
    }

    protected function createStudent($attribs = [])
    {
        return factory(\App\User::class)->states('student')->create($attribs);
    }

    protected function createStaff($attribs = [])
    {
        return factory(\App\User::class)->states('staff')->create($attribs);
    }

    protected function createAdmin($attribs = [])
    {
        return factory(\App\User::class)->states('admin')->create($attribs);
    }

    protected function createProject($attribs = [])
    {
        return factory(\App\Project::class)->create($attribs);
    }

    protected function createCourse($attribs = [])
    {
        return factory(\App\Course::class)->create($attribs);
    }

    protected function createDiscipline($attribs = [])
    {
        return factory(\App\Discipline::class)->create($attribs);
    }
}
