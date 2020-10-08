<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\ProjectConfig;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AdminConfigOptionsTest extends TestCase
{
    /** @test */
    public function admin_can_set_project_system_options()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('options.update'), [
            'logins_allowed' => true,
            'round' => 1,
            'applications_allowed' => true,
            'single_uog_required_choices' => 0,
            'single_uestc_required_choices' => 9,
            'dual_uog_required_choices' => 3,
            'dual_uestc_required_choices' => 6,
            'maximum_applications' => 6,
            'project_edit_start' => Carbon::now()->format('d/m/Y'),
            'project_edit_end' => Carbon::now()->addDays(7)->format('d/m/Y'),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('options.edit'));
        $this->assertDatabaseHas('project_configs', ['key' => 'logins_allowed', 'value' => true]);
        $this->assertDatabaseHas('project_configs', ['key' => 'required_choices', 'value' => 3]);
        $this->assertDatabaseHas('project_configs', ['key' => 'single_uog_required_choices', 'value' => 0]);
        $this->assertDatabaseHas('project_configs', ['key' => 'single_uestc_required_choices', 'value' => 9]);
        $this->assertDatabaseHas('project_configs', ['key' => 'maximum_applications', 'value' => 6]);
        $this->assertDatabaseHas('project_configs', ['key' => 'round', 'value' => 1]);
        $this->assertDatabaseHas('project_configs', ['key' => 'project_edit_start', 'value' => Carbon::now()->format('d/m/Y')]);
        $this->assertDatabaseHas('project_configs', ['key' => 'project_edit_end', 'value' => Carbon::now()->addDays(7)->format('d/m/Y')]);
        $this->assertDatabaseHas('project_configs', ['key' => 'applications_allowed', 'value' => true]);
    }

    /** @test */
    public function admin_can_delete_all_project_allocations()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $project = $this->createProject();

        $project->addStudent($student);
        $this->assertDatabaseHas('project_rounds', ['project_id' => $project->id, 'user_id' => $student->id]);

        $this->assertDatabaseHas('project_student', ['user_id' => $student->id, 'project_id' => $project->id]);

        $response = $this->actingAs($admin)->get(route('options.allocations.destroy'));

        $this->assertDatabaseMissing('project_student', ['user_id' => $student->id, 'project_id' => $project->id]);
        $this->assertDatabaseMissing('project_rounds', ['user_id' => $student->id, 'project_id' => $project->id]);
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }
}
