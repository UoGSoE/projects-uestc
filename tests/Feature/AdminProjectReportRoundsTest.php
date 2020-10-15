<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Models\ProjectConfig;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AdminProjectReportRoundsTest extends TestCase
{
    /** @test */
    public function admin_can_see_project_report_with_correct_rounds_stats()
    {
        $project = $this->createProject();
        $admin = $this->createAdmin();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $student3 = $this->createStudent();
        ProjectConfig::setOption('round', 1);
        $project->addStudent($student1);
        $project->addStudent($student2);
        $project->removeUnsucessfulStudents();
        ProjectConfig::setOption('round', 2);
        $project->acceptStudent($student3);

        $response = $this->actingAs($admin)->get(route('report.projects'));

        $response->assertStatus(200);
        $response->assertSee((string) $project->students()->count());
    }
}
