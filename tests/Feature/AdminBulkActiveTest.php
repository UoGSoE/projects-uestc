<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AdminBulkActiveTest extends TestCase
{
    /** @test */
    public function admin_can_change_project_active_flags_in_bulk()
    {
        $project1 = $this->createProject(['is_active' => false]);
        $project2 = $this->createProject(['is_active' => true]);
        $admin = $this->createAdmin();
        $data['statuses'][$project1->id] = true;
        $data['statuses'][$project2->id] = false;

        $response = $this->actingAs($admin)->post(route('bulkactive.update'), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success_message', 'Changes saved');
        $this->assertDatabaseHas('projects', ['id' => $project1->id, 'is_active' => true]);
        $this->assertDatabaseHas('projects', ['id' => $project2->id, 'is_active' => false]);
    }
}
