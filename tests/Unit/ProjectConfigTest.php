<?php

// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\ProjectConfig;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProjectConfigTest extends TestCase
{
    /** @test */
    public function setting_an_option_works()
    {
        ProjectConfig::setOption('testkey', 'testvalue');

        $value = ProjectConfig::getOption('testkey');

        $this->assertEquals('testvalue', $value);
    }

    /** @test */
    public function setting_a_value_twice_doesnt_create_duplicates()
    {
        ProjectConfig::setOption('testkey', 'testvalue');
        ProjectConfig::setOption('testkey', 'testvalue');

        $results = ProjectConfig::where('key', '=', 'testkey')->get();

        $this->assertEquals(1, $results->count());
    }

    /** @test */
    public function getting_an_existing_value_while_passing_a_default_returns_the_actual_value()
    {
        ProjectConfig::setOption('testkey', 'testvalue');

        $value = ProjectConfig::getOption('testkey', 'DEFAULT');

        $this->assertEquals('testvalue', $value);
    }

    /** @test */
    public function getting_a_non_existant_value_throws_an_exception_if_no_default_given()
    {
        try {
            $value = ProjectConfig::getOption('testkey');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);

            return;
        }

        $this->fail('Getting a non-existant value with no default passed did not throw an Exception');
    }

    /** @test */
    public function getting_a_non_existant_value_returns_the_default_when_given()
    {
        $value = ProjectConfig::getOption('testkey', 'DEFAULT');

        $this->assertEquals('DEFAULT', $value);
    }
}
