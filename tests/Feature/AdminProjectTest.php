<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Project;
use App\ProjectConfig;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

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

        $response = $this->actingAs($admin)->fromUrl(route('report.projects'))->post(route('admin.clear_unsuccessful'));

        $response->assertStatus(302);
        $response->assertRedirect(route('report.projects'));
        $this->assertEquals(0, $project1->students()->count());
        $this->assertEquals(1, $project2->students()->count());
    }

    /** @test */
    public function manually_allocating_a_student_to_a_project_sets_the_preallocated_flag()
    {
        ProjectConfig::setOption('round', 1);
        $project = $this->createProject();
        $student = $this->createStudent();
        $admin = $this->createAdmin();
        $course = $this->createCourse();

        $response = $this->actingAs($admin)->post(route('project.update', $project->id), [
            'title' => 'HELLO',
            'description' => 'THERE',
            'courses' => [$course->id],
            'maximum_students' => 1,
            'user_id' => $project->user_id,
            'student_id' => $student->id
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertTrue($project->fresh()->manually_allocated);
    }

    /** @test */
    public function student_can_be_manually_removed_from_a_project () {
        Notification::fake();
        ProjectConfig::setOption('round', 1);
        $project = $this->createProject();
        $student = $this->createStudent();
        $admin = $this->createAdmin();
        $course = $this->createCourse();

        $project->preAllocate($student);

        $this->assertTrue($project->fresh()->manually_allocated);
        $this->assertEquals(1, $project->numberAccepted());

        $response = $this->actingAs($admin)->post(route('student.unallocate', $student->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('user.show', $student->id));
        $this->assertFalse($project->fresh()->manually_allocated);
        $this->assertEquals(0, $project->numberAccepted());
    }

    /** @test */
    public function get_project_json_via_api () {
        ProjectConfig::setOption('round', 1);
        $project = $this->createProject();
        factory(Project::class, 10)->create();

        $response = $this->get(route('api.projects'));

        $response->assertStatus(200);
        $response->assertSee($project->title);
        $response->assertSee($project->description);
    }
}
