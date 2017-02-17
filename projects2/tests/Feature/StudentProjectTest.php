<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Course;
use App\Project;

class StudentProjectTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_student_can_see_applicable_available_projects()
    {
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $project = factory(Project::class)->create(['maximum_students' => 1]);
        $project->courses()->save($course);

        $response = $this->actingAs($student)
                        ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Available Projects');
        $response->assertSee($project->title);
    }

    public function test_student_cant_see_projects_which_already_have_the_maximum_number_of_students_accepted()
    {
        $student = factory(User::class)->states('student')->create();
        $student2 = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $project = factory(Project::class)->create(['maximum_students' => 1]);
        $project->courses()->save($course);
        $project->students()->save($student2);
        $project->acceptStudent($student2);

        $response = $this->actingAs($student)
                        ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Available Projects');
        $response->assertDontSee($project->title);
    }

    public function test_student_cant_see_projects_which_the_maximum_number_have_already_applied()
    {
        $student = factory(User::class)->states('student')->create();
        $otherStudents = factory(User::class, config('projects.maximumAllowedToApply'))->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $project = factory(Project::class)->create(['maximum_students' => 1]);
        $project->courses()->save($course);
        $project->students()->saveMany($otherStudents);

        $response = $this->actingAs($student)
                        ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Available Projects');
        $response->assertDontSee($project->title);
    }

    public function test_a_student_must_apply_for_the_required_number_of_projects()
    {
        $student = factory(User::class)->states('student')->create();
        $project = factory(Project::class)->create();
        $required = config('requiredProjectChoices');

        $response = $this->actingAs($student)
                        ->post(route('choices.update'), ['choices' => []]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)
                        ->post(route('choices.update'), ['choices' => range(1, $required + 1)]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);
    }

    public function test_a_student_can_successfully_apply_for_available_projects()
    {
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $projectIds = $projects->pluck('id')->toArray();
        $response = $this->actingAs($student)
                        ->post(route('choices.update', ['choices' => $projectIds]));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
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
        $response->assertSee('Create A New Project');
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
}
