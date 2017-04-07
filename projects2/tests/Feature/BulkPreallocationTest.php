<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\ProjectConfig;

class BulkPreallocationTest extends TestCase
{
    /** @test */
    public function admins_can_see_the_bulk_preallocation_page()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();

        $response = $this->actingAs($admin)->get(route('bulkpreallocate.edit'));

        $response->assertStatus(200);
        $response->assertSee('Bulk Preallocation');
        $response->assertSee($project1->title);
        $response->assertSee($project2->title);
        $response->assertSee($student1->fullName());
        $response->assertSee($student2->fullName());
    }

    /** @test */
    public function admins_can_preallocate_students_in_bulk()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();

        $response = $this->actingAs($admin)->post(route('bulkpreallocate.update'), [
            'project' => [
                $project1->id => $student1->id,
                $project2->id => $student2->id,
            ]
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('project_student', [
            'project_id' => $project1->id,
            'user_id' => $student1->id,
            'accepted' => true,
        ]);
        $this->assertDatabaseHas('project_student', [
            'project_id' => $project2->id,
            'user_id' => $student2->id,
            'accepted' => true,
        ]);
    }
}
