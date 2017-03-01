<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminBulkAllocateTest extends TestCase
{
    /** @test */
    public function admin_can_bulk_allocate_students()
    {
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
}
