<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Course;
use App\Project;
use App\Discipline;

class StudentProjectTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_student_can_see_applicable_available_projects()
    {
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $discipline = factory(Discipline::class)->create();
        $project = factory(Project::class)->create(['maximum_students' => 1, 'discipline_id' => $discipline->id]);
        $project->courses()->save($course);
        $disabledProject = factory(Project::class)->create(['is_active' => false]);
        $disabledProject->courses()->save($course);
        $projectNotForStudentsCourse = factory(Project::class)->create();

        $response = $this->actingAs($student)
                        ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Available Projects');
        $response->assertSee($project->title);
        $response->assertSee($project->discipline->title);
        $response->assertDontSee($disabledProject->title);
        $response->assertDontSee($projectNotForStudentsCourse->title);
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

    /**
     * This is to check for a race condition.  Students all get told to pick at the same time,
     * so if one has the list of projects open for a while - other students may have filled
     * up their choice in the meantime
     */
    public function test_a_student_cant_apply_for_projects_which_are_fully_subscribed()
    {
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $otherStudents = factory(User::class, config('projects.maximumAllowedToApply'))->states('student')->create();
        $firstProject = Project::first();
        $firstProject->students()->saveMany($otherStudents);

        $projectIds = $projects->pluck('id')->toArray();
        $response = $this->actingAs($student)
                        ->post(route('choices.update', ['choices' => $projectIds]));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['oversubscribed']);
    }

    /**
     * Another race condition to check. If the student takes ages to pick their choices, one of them
     * might have had someone apply and be accepted - so they should get knocked back.
     */
    public function test_a_student_cant_apply_for_projects_which_are_full()
    {
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $otherStudent = factory(User::class)->states('student')->create();
        $firstProject = Project::first();
        $firstProject->acceptStudent($otherStudent);

        $projectIds = $projects->pluck('id')->toArray();
        $response = $this->actingAs($student)
                        ->post(route('choices.update', ['choices' => $projectIds]));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['full']);
    }

    public function test_a_student_cant_apply_for_projects_when_in_read_only_mode()
    {
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $projectIds = $projects->pluck('id')->toArray();

        \Artisan::call('projects:allowapplications', ['flag' => 'no']);

        $response = $this->actingAs($student)
                        ->post(route('choices.update', ['choices' => $projectIds]));
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['disabled']);

        \Artisan::call('projects:allowapplications', ['flag' => 'yes']);
    }
}
