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
use App\ProjectConfig;

class StudentProjectTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_student_can_see_applicable_available_projects()
    {
        ProjectConfig::setOption('round', 1);
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
        ProjectConfig::setOption('round', 1);
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
        ProjectConfig::setOption('round', 1);
        $otherStudents = factory(User::class, 6)->states('student')->create();
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $project = factory(Project::class)->create(['maximum_students' => 1]);
        $project->courses()->save($course);
        $project->students()->saveMany($otherStudents);

        ProjectConfig::setOption('maximum_applications', 6);
        $response = $this->actingAs($student)
                        ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Available Projects');
        $response->assertDontSee($project->title);
    }

    public function test_a_student_must_apply_for_the_required_number_of_projects()
    {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create();
        $project = factory(Project::class)->create();
        $required = 3;

        ProjectConfig::setOption('required_choices', $required);
        $response = $this->actingAs($student)
                        ->post(route('choices.update'), ['choices' => range(1, $required - 1)]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)
                        ->post(route('choices.update'), ['choices' => range(1, $required + 1)]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);
    }

    /** @test */
    public function a_student_must_apply_for_correct_number_of_uog_and_uestc_projects () {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create();
        factory(Project::class, 3)->create();
        factory(Project::class, 6)->create(['institution' => 'UESTC']);

        $response = $this->actingAs($student)->post(route('choices.update'), ['choices' => range(1, 8)]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)->post(route('choices.update'), ['choices' => [1,2,4,5,6,7,8,9]]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)->post(route('choices.update'), ['choices' => [1,2,3,5,6,7,8,9]]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)->post(route('choices.update'), ['choices' => range(1,9)]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas(['success_message' => 'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.']);

    }

    public function test_a_student_can_successfully_apply_for_available_projects()
    {
        ProjectConfig::setOption('round', 1);
        ProjectConfig::setOption('uestc_required_choices', 0);
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

    public function test_a_student_can_resubmit_and_change_their_choices()
    {
        ProjectConfig::setOption('round', 1);
        ProjectConfig::setOption('uestc_required_choices', 0);
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $otherProjects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course, $student) {
            $project->courses()->save($course);
            $project->addStudent($student);
        });
        $otherProjects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $projectIds = $otherProjects->pluck('id')->toArray();
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
        ProjectConfig::setOption('round', 1);
        ProjectConfig::setOption('uestc_required_choices', 0);
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
        ProjectConfig::setOption('round', 1);
        ProjectConfig::setOption('uestc_required_choices', 0);
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
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $projectIds = $projects->pluck('id')->toArray();

        ProjectConfig::setOption('applications_allowed', 0);
        $response = $this->actingAs($student)
                        ->post(route('choices.update', ['choices' => $projectIds]));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['disabled']);
    }

    /** @test */
    public function a_student_who_has_been_accepted_onto_a_project_only_sees_that_project()
    {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $project1 = factory(Project::class)->create(['maximum_students' => 1]);
        $project2 = factory(Project::class)->create(['maximum_students' => 1]);
        $project1->courses()->save($course);
        $project2->courses()->save($course);
        $project1->acceptStudent($student);

        $response = $this->actingAs($student)
                        ->get('/');

        $response->assertStatus(200);
        $response->assertSee('You are allocated to the project');
        $response->assertSee($project1->title);
        $response->assertSee($project1->description);
        $response->assertDontSee($project2->title);
    }

    /** @test */
    public function a_student_who_has_made_their_choices_only_sees_those_projects()
    {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.requiredProjectChoices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course, $student) {
            $project->courses()->save($course);
            $project->addStudent($student);
        });
        $project2 = factory(Project::class)->create(['maximum_students' => 1]);
        $project2->courses()->save($course);


        $response = $this->actingAs($student)
                        ->get('/');

        $response->assertStatus(200);
        $response->assertSee('Your choices');
        $projects->each(function ($project) use ($response) {
            $response->assertSee($project->title);
        });
        $response->assertDontSee($project2->title);
    }
}
