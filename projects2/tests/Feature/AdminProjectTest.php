<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Project;
use Tests\TestCase;
use App\ProjectConfig;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminProjectTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_see_all_projects()
    {
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();

        $response = $this->actingAs($admin)->get(route('report.projects'));

        $response->assertStatus(200);
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
    }

    public function test_admin_can_toggle_whether_students_can_apply()
    {
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();

        $response = $this->actingAs($admin)->post(route('admin.deny_applications'));
        $response->assertStatus(302);
        $response = $this->actingAs($admin)->get(route('report.projects'));
        $response->assertSee('Enable Applications');
        $this->assertFalse(Project::applicationsEnabled());

        $response = $this->actingAs($admin)->post(route('admin.allow_applications'));
        $response->assertStatus(302);
        $response = $this->actingAs($admin)->get(route('report.projects'));
        $response->assertSee('Deny Applications');
        $this->assertTrue(Project::applicationsEnabled());
    }

    public function test_admin_can_clear_all_unsuccessful_student_applications()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $project1->addStudent($student1);
        $project1->addStudent($student2);
        $project2->addStudent($student1);
        $project2->addStudent($student2);
        $project2->acceptStudent($student2);

        $response = $this->actingAs($admin)->from(route('report.projects'))->post(route('admin.clear_unsuccessful'));

        $response->assertStatus(302);
        $response->assertRedirect(route('report.projects'));
        $this->assertEquals(0, $project1->students()->count());
        $this->assertEquals(1, $project2->students()->count());
    }

}
