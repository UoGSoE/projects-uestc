<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Project;
use App\ProjectConfig;

class AllowApplicationTest extends TestCase
{
    public function test_setting_project_config_flag_controls_application_enabling()
    {
        ProjectConfig::setOption('applications_allowed', 0);
        $this->assertFalse(Project::applicationsEnabled());

        ProjectConfig::setOption('applications_allowed', 1);
        $this->assertTrue(Project::applicationsEnabled());
    }
}
