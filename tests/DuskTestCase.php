<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
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
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
        ]);

        return RemoteWebDriver::create(
            'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY,
                $options
            )
        );
    }

    protected function createProjectFile($attribs)
    {
        return \App\Models\ProjectFile::factory()->create($attribs);
    }

    protected function createProjectLink($attribs)
    {
        return \App\Models\ProjectLink::factory()->create($attribs);
    }

    protected function createStudent($attribs = [])
    {
        return \App\Models\User::factory()->student()->create($attribs);
    }

    protected function createStaff($attribs = [])
    {
        return \App\Models\User::factory()->staff()->create($attribs);
    }

    protected function createAdmin($attribs = [])
    {
        return \App\Models\User::factory()->admin()->create($attribs);
    }

    protected function createProject($attribs = [])
    {
        return \App\Models\Project::factory()->create($attribs);
    }

    protected function createCourse($attribs = [])
    {
        return \App\Models\Course::factory()->create($attribs);
    }

    protected function createDiscipline($attribs = [])
    {
        return \App\Models\Discipline::factory()->create($attribs);
    }
}
