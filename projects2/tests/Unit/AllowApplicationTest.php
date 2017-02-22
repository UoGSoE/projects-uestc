<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Project;

class AllowApplicationTest extends TestCase
{
    public function test_calling_artisan_command_creates_the_correct_file()
    {
        \Artisan::call('projects:allowapplications', ['flag' => 'no']);
        $this->assertTrue(file_exists(storage_path('app/projects.disabled')));
        $this->assertFalse(Project::applicationsEnabled());

        \Artisan::call('projects:allowapplications', ['flag' => 'yes']);
        $this->assertFalse(file_exists(storage_path('app/projects.disabled')));
        $this->assertTrue(Project::applicationsEnabled());
    }
}
