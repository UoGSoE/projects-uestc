<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Project;
use App\ProjectConfig;

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
        $response->assertSee($project1->owner->fullName());
        $response->assertSee($project2->owner->fullName());
        $response->assertDontSee($project3->owner->fullName());
        $response->assertSee("round_1_student_{$student1->id}_accepted_0");
        $response->assertSee("round_2_student_{$student1->id}_accepted_1");
        $response->assertSee("round_1_student_{$student2->id}_accepted_1");
        $response->assertSee("round_2_student_{$student2->id}_accepted_0");
        $response->assertSee("round_1_student_{$student3->id}_accepted_0");
        $response->assertSee("round_2_student_{$student3->id}_accepted_0");
    }
}
