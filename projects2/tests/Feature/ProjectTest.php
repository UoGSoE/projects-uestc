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

    public $regularUser;

    public function test_staff_can_create_a_new_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.store'), $this->defaultProjectData());

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'DEFAULTTITLE']);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
    }

    public function test_staff_can_edit_their_own_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'UPDATEDPROJECT']);
        $this->assertDatabaseMissing('projects', ['title' => $project->title]);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
    }

    public function test_staff_cant_edit_someone_elses_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $user2 = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['title' => $project->title]);
    }

    public function test_admin_can_edit_someone_elses_project()
    {
        $admin = factory(User::class)->states('admin')->create();
        $user2 = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($admin)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'UPDATEDPROJECT']);
    }

    public function test_staff_can_delete_their_own_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->delete(route('project.destroy', $project->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('projects', ['title' => $project->title]);
        $response->assertRedirect('/');
    }

    public function test_staff_cant_delete_someone_elses_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $user2 = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($user2)
                        ->delete(route('project.destroy', $project->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['title' => $project->title]);
    }

    public function test_staff_can_make_a_copy_of_their_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->get(route('project.copy', $project->id));

        $response->assertStatus(200);
        $response->assertSee('Create a new project');
        $response->assertSee($project->title);
    }


    protected function defaultProjectData($overrides = [])
    {
        $course = factory(Course::class)->create();
        return array_merge([
            'title' => 'DEFAULTTITLE',
            'description' => 'DEFAULTDESCRIPTION',
            'is_active' => true,
            'user_id' => $this->regularUser ? $this->regularUser->id : 1,
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
