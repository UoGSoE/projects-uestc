<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Course;
use App\Project;

class ProjectTest extends TestCase
{
    use DatabaseMigrations;

    public function test_staff_can_create_a_new_project()
    {
        $regularUser = factory(User::class)->states('staff')->create();
        $response = $this->actingAs($regularUser)
                        ->post(route('project.store', $this->defaultProjectData()));
        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'DEFAULTTITLE']);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
    }

    protected function defaultProjectData($overrides = [])
    {
        $course = factory(Course::class)->create();
        return array_merge([
            'title' => 'DEFAULTTITLE',
            'description' => 'DEFAULTDESCRIPTION',
            'is_active' => true,
            'user_id' => 1,
            'maximum_students' => 1,
            'courses' => [1 => $course->id],
        ], $overrides);
    }

    public function test_admin_can_view_current_staff()
    {
        $adminUser = factory(User::class)->states('admin')->create();
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($adminUser)
                        ->get(route('staff.index'));

        $response->assertStatus(200);
        $response->assertSee('Current Staff');
        $response->assertSee($adminUser->username);
        $response->assertSee($regularUser->username);
    }

    public function test_admin_can_create_a_new_user()
    {
        $adminUser = factory(User::class)->states('admin')->create();

        $response = $this->actingAs($adminUser)
                        ->post(route('user.store'), [
                            'username' => 'HELLOKITTY',
                            'surname' => 'Kitty',
                            'forenames' => 'Hello',
                            'is_student' => false,
                            'email' => 'hellokitty@example.com'
                        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'hellokitty@example.com']);
    }

    public function test_admin_can_edit_an_existing_user()
    {
        $adminUser = factory(User::class)->states('admin')->create();
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($adminUser)
                        ->post(route('user.update', $regularUser->id), [
                            'username' => 'HELLOKITTY',
                            'surname' => 'Kitty',
                            'forenames' => 'Hello',
                            'is_student' => false,
                            'email' => 'hellokitty@example.com'
                        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'hellokitty@example.com']);
        $this->assertDatabaseMissing('users', ['email' => $regularUser->email]);
    }

    public function test_admin_can_delete_a_user()
    {
        $adminUser = factory(User::class)->states('admin')->create();
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($adminUser)->delete(route('user.destroy', $regularUser->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', ['email' => $regularUser->email]);
    }
}
