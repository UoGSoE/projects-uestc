<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use App\ProjectConfig;
use App\Notifications\AllocatedToProject;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminBulkAllocateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_bulk_allocate_students()
    {
        if (env("CI")) {
            $this->markTestSkipped('Not doing ldap stuff in CI');
        }

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
        $data['student'][$student1->id] = $project1->id;
        $data['student'][$student2->id] = $project2->id;

        $response = $this->actingAs($admin)->post(route('bulkallocate.update'), $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('bulkallocate.edit'));
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('project_student', ['project_id' => $project1->id, 'user_id' => $student1->id, 'accepted' => true]);
        $this->assertDatabaseHas('project_student', ['project_id' => $project2->id, 'user_id' => $student2->id, 'accepted' => true]);
    }

    /** @test */
    public function notifications_are_sent_to_students_when_bulk_allocated_to_projects()
    {
        ProjectConfig::setOption('round', 1);
        Notification::fake();
        $admin = $this->createAdmin();
        $project = $this->createProject();
        $student = $this->createStudent();
        $project->addStudent($student);
        $data['student'][$student->id] = $project->id;

        $response = $this->actingAs($admin)->post(route('bulkallocate.update'), $data);

        Notification::assertSentTo(
            $student,
            AllocatedToProject::class,
            function ($notification, $channels) use ($project) {
                return $notification->project->id === $project->id;
            }
        );
    }

    /** @test */
    public function admin_cant_bulk_allocate_more_students_to_a_project_than_allowed()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject(['maximum_students' => 1]);
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $project1->addStudent($student1);
        $project1->addStudent($student2);
        $data['student'][$student1->id] = $project1->id;
        $data['student'][$student2->id] = $project1->id;

        $response = $this->actingAs($admin)->post(route('bulkallocate.update'), $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('bulkallocate.edit'));
        $response->assertSessionHasErrors(['oversubscribed']);
        $this->assertDatabaseHas('project_student', ['project_id' => $project1->id, 'user_id' => $student1->id, 'accepted' => true]);
        $this->assertDatabaseMissing('project_student', ['project_id' => $project1->id, 'user_id' => $student2->id, 'accepted' => true]);
    }
}
