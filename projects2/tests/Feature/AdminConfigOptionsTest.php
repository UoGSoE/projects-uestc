<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

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
            'required_choices' => 3,
            'maximum_applications' => 6,
            'uestc_required_choices' => 6,
            'project_edit_start' => Carbon::now()->format('d/m/Y'),
            'project_edit_end' => Carbon::now()->addDays(7)->format('d/m/Y'),
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('options.edit'));
        $this->assertDatabaseHas('project_configs', ['key' => 'logins_allowed', 'value' => true]);
        $this->assertDatabaseHas('project_configs', ['key' => 'required_choices', 'value' => 3]);
        $this->assertDatabaseHas('project_configs', ['key' => 'maximum_applications', 'value' => 6]);
        $this->assertDatabaseHas('project_configs', ['key' => 'round', 'value' => 1]);
        $this->assertDatabaseHas('project_configs', ['key' => 'project_edit_start', 'value' => Carbon::now()->format('d/m/Y')]);
        $this->assertDatabaseHas('project_configs', ['key' => 'project_edit_end', 'value' => Carbon::now()->addDays(7)->format('d/m/Y')]);
        $this->assertDatabaseHas('project_configs', ['key' => 'applications_allowed', 'value' => true]);
    }
}
