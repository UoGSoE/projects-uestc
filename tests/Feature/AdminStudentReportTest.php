<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Project;
use App\ProjectConfig;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AdminStudentReportTest extends TestCase
{
    /** @test */
    public function student_report_shows_correct_round_and_accepted_project_information()
    {
        /*
            Somewhat complicated setup for this report.
            Three students, three projects. Student2 is accepted on round 1, student1 is
            accepted on round 2, student 3 is never accepted.
            So we have to check that the students are marked correctly and
            that their accepted projects show up.
        */
        $admin = $this->createAdmin();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $student3 = $this->createStudent();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $project3 = $this->createProject();
        ProjectConfig::setOption('round', 1);
        $project1->acceptStudent($student2);
        ProjectConfig::setOption('round', 2);
        $project2->acceptStudent($student1);

        $response = $this->actingAs($admin)->get(route('report.students'));

        $response->assertStatus(200);
        $response->assertSee($student1->fullName());
        $response->assertSee($student2->fullName());
        $response->assertSee($student3->fullName());
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
        $response->assertDontSee($project3->title);
    }

    /** @test */
    public function admin_can_remove_all_allocations_of_projects_from_students()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $project1->acceptStudent($student1);
        $project2->acceptStudent($student2);

        $response = $this->actingAs($admin)->get(route('options.allocations.destroy'));

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $response->assertRedirect(route('options.edit'));
        $this->assertDatabaseHas('users', ['id' => $student1->id]);
        $this->assertDatabaseMissing('project_student', ['user_id' => $student1->id, 'project_id' => $project1->id]);
        $this->assertDatabaseMissing('project_student', ['user_id' => $student2->id, 'project_id' => $project2->id]);
    }
}
