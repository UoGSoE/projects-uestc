<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Course;
use App\Discipline;
use App\Project;
use App\ProjectConfig;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

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
            ->post(route('choices.update'), ['uogChoices' => implode(',', range(1, $required - 1))]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)
            ->post(route('choices.update'), ['uogChoices' => implode(',', range(1, $required + 1))]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);
    }

    /** @test */
    public function a_student_must_apply_for_correct_number_of_uog_and_uestc_projects()
    {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create();
        factory(Project::class, 3)->create();
        factory(Project::class, 6)->create(['institution' => 'UESTC']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => implode(',', range(1, 3)),
            'uestcChoices' => implode(',', range(4, 8)),
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => implode(',', range(1, 2)),
            'uestcChoices' => implode(',', range(4, 8)),
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => implode(',', range(1, 3)),
            'uestcChoices' => implode(',', range(4, 5)),
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['choice_number']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => implode(',', range(1, 3)),
            'uestcChoices' => implode(',', range(4, 9)),
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas(['success_message' => 'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.']);
    }

    /** @test */
    public function a_student_must_apply_for_projects_that_do_not_have_the_same_supervisor()
    {
        ProjectConfig::setOption('round', 1);
        config(['projects.uestc_unique_supervisors' => true]);
        config(['projects.uog_unique_supervisors' => true]);
        $supervisor = factory(User::class)->states('staff')->create();
        $student = factory(User::class)->states('student')->create();
        $project1 = factory(Project::class)->create(['user_id' => $supervisor->id]);
        $project2 = factory(Project::class)->create(['user_id' => $supervisor->id]);
        factory(Project::class)->create();
        factory(Project::class, 6)->create(['institution' => 'UESTC']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => implode(',', range(1, 3)),
            'uestcChoices' => implode(',', range(4, 9)),
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['supervisor_diff']);
    }

    /** @test */
    public function uog_supervisors_must_be_unique_but_uestc_do_not()
    {
        ProjectConfig::setOption('round', 1);
        config(['projects.uestc_unique_supervisors' => false]);
        config(['projects.uog_unique_supervisors' => true]);
        $uogSupervisor = factory(User::class)->states('staff')->create();
        $uestcSupervisor = factory(User::class)->states('staff')->create();
        $student = factory(User::class)->states('student')->create();
        $uniqueUogProjects = factory(Project::class, 3)->create(['institution' => 'UoG']);
        $sameSupUogProjects = factory(Project::class, 3)->create([
            'user_id' => $uogSupervisor->id,
            'institution' => 'UoG',
        ]);
        $uniqueUestcProjects = factory(Project::class, 6)->create(['institution' => 'UESTC']);
        $sameSupUestcProjects = factory(Project::class, 6)->create([
            'user_id' => $uestcSupervisor->id,
            'institution' => 'UESTC',
        ]);

        $uniqueUogIds = implode(',', $uniqueUogProjects->pluck('id')->toArray());
        $sameSupUogIds = implode(',', $sameSupUogProjects->pluck('id')->toArray());
        $uniqueUestcIds = implode(',', $uniqueUestcProjects->pluck('id')->toArray());
        $sameSupUestcIds = implode(',', $sameSupUestcProjects->pluck('id')->toArray());

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $sameSupUogIds,
            'uestcChoices' => $sameSupUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['supervisor_diff']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $sameSupUogIds,
            'uestcChoices' => $uniqueUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['supervisor_diff']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $uniqueUogIds,
            'uestcChoices' => $sameSupUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $uniqueUogIds,
            'uestcChoices' => $uniqueUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);
    }

    /** @test */
    public function uestc_supervisors_must_be_unique_but_uog_do_not()
    {
        ProjectConfig::setOption('round', 1);
        config(['projects.uestc_unique_supervisors' => true]);
        config(['projects.uog_unique_supervisors' => false]);
        $uogSupervisor = factory(User::class)->states('staff')->create();
        $uestcSupervisor = factory(User::class)->states('staff')->create();
        $student = factory(User::class)->states('student')->create();
        $uniqueUogProjects = factory(Project::class, 3)->create(['institution' => 'UoG']);
        $sameSupUogProjects = factory(Project::class, 3)->create([
            'user_id' => $uogSupervisor->id,
            'institution' => 'UoG',
        ]);
        $uniqueUestcProjects = factory(Project::class, 6)->create(['institution' => 'UESTC']);
        $sameSupUestcProjects = factory(Project::class, 6)->create([
            'user_id' => $uestcSupervisor->id,
            'institution' => 'UESTC',
        ]);

        $uniqueUogIds = implode(',', $uniqueUogProjects->pluck('id')->toArray());
        $sameSupUogIds = implode(',', $sameSupUogProjects->pluck('id')->toArray());
        $uniqueUestcIds = implode(',', $uniqueUestcProjects->pluck('id')->toArray());
        $sameSupUestcIds = implode(',', $sameSupUestcProjects->pluck('id')->toArray());

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $sameSupUogIds,
            'uestcChoices' => $sameSupUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['supervisor_diff']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $uniqueUogIds,
            'uestcChoices' => $sameSupUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['supervisor_diff']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $sameSupUogIds,
            'uestcChoices' => $uniqueUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $uniqueUogIds,
            'uestcChoices' => $uniqueUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);
    }

    /** @test */
    public function supervisors_do_not_have_to_be_unique()
    {
        ProjectConfig::setOption('round', 1);
        config(['projects.uestc_unique_supervisors' => false]);
        config(['projects.uog_unique_supervisors' => false]);
        $uogSupervisor = factory(User::class)->states('staff')->create();
        $uestcSupervisor = factory(User::class)->states('staff')->create();
        $student = factory(User::class)->states('student')->create();
        $uniqueUogProjects = factory(Project::class, 3)->create(['institution' => 'UoG']);
        $sameSupUogProjects = factory(Project::class, 3)->create([
            'user_id' => $uogSupervisor->id,
            'institution' => 'UoG',
        ]);
        $uniqueUestcProjects = factory(Project::class, 6)->create(['institution' => 'UESTC']);
        $sameSupUestcProjects = factory(Project::class, 6)->create([
            'user_id' => $uestcSupervisor->id,
            'institution' => 'UESTC',
        ]);

        $uniqueUogIds = implode(',', $uniqueUogProjects->pluck('id')->toArray());
        $sameSupUogIds = implode(',', $sameSupUogProjects->pluck('id')->toArray());
        $uniqueUestcIds = implode(',', $uniqueUestcProjects->pluck('id')->toArray());
        $sameSupUestcIds = implode(',', $sameSupUestcProjects->pluck('id')->toArray());

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $sameSupUogIds,
            'uestcChoices' => $sameSupUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $uniqueUogIds,
            'uestcChoices' => $sameSupUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $sameSupUogIds,
            'uestcChoices' => $uniqueUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);

        $response = $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => $uniqueUogIds,
            'uestcChoices' => $uniqueUestcIds,
        ]);
        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionMissing(['errors']);
    }

    public function test_a_student_can_successfully_apply_for_available_projects()
    {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create();
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.uog_required_choices'))->create(['maximum_students' => 1]);
        $uestcProjects = factory(Project::class, config('projects.uestc_required_choices'))->create(['maximum_students' => 1, 'institution' => 'UESTC']);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $uestcProjects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $projectIds = $projects->pluck('id')->toArray();
        $uestcProjectIds = $uestcProjects->pluck('id')->toArray();
        $response = $this->actingAs($student)
            ->post(route('choices.update', [
                'uogChoices' => implode(',', $projectIds),
                'uestcChoices' => implode(',', $uestcProjectIds),
            ]));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
    }

    public function test_a_student_can_resubmit_and_change_their_choices()
    {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create(['degree_type' => 'Dual']);
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.uog_required_choices'))->create(['maximum_students' => 1]);
        $uestcProjects = factory(Project::class, config('projects.uestc_required_choices'))->create(['maximum_students' => 1, 'institution' => 'UESTC']);
        $otherProjects = factory(Project::class, config('projects.uog_required_choices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course, $student) {
            $project->courses()->save($course);
            $project->addStudent($student);
        });
        $uestcProjects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $otherProjects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $projectIds = $otherProjects->pluck('id')->toArray();
        $uestcProjectIds = $uestcProjects->pluck('id')->toArray();
        $response = $this->actingAs($student)
            ->post(route('choices.update', [
                'uogChoices' => implode(',', $projectIds),
                'uestcChoices' => implode(',', $uestcProjectIds),
            ]));

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
    }

    /**
     * This is to check for a race condition.  Students all get told to pick at the same time,
     * so if one has the list of projects open for a while - other students may have filled
     * up their choice in the meantime.
     */
    // public function test_a_student_cant_apply_for_projects_which_are_fully_subscribed()
    // {
    //     ProjectConfig::setOption('round', 1);
    //     ProjectConfig::setOption('uestc_required_choices', 0);
    //     $student = factory(User::class)->states('student')->create();
    //     $course = factory(Course::class)->create();
    //     $course->students()->save($student);
    //     $projects = factory(Project::class, config('projects.uog_required_choices'))->create(['maximum_students' => 1]);
    //     $projects->each(function ($project, $key) use ($course) {
    //         $project->courses()->save($course);
    //     });
    //     $otherStudents = factory(User::class, config('projects.maximumAllowedToApply'))->states('student')->create();
    //     $firstProject = Project::first();
    //     $firstProject->students()->saveMany($otherStudents);

    //     $projectIds = $projects->pluck('id')->toArray();
    //     $response = $this->actingAs($student)
    //                     ->post(route('choices.update', ['choices' => $projectIds]));

    //     $response->assertStatus(302);
    //     $response->assertRedirect('/');
    //     $response->assertSessionHasErrors(['oversubscribed']);
    // }

    /**
     * Another race condition to check. If the student takes ages to pick their choices, one of them
     * might have had someone apply and be accepted - so they should get knocked back.
     */
    public function test_a_student_cant_apply_for_projects_which_are_full()
    {
        ProjectConfig::setOption('round', 1);
        $student = factory(User::class)->states('student')->create(['degree_type' => 'Dual']);
        $course = factory(Course::class)->create();
        $course->students()->save($student);
        $projects = factory(Project::class, config('projects.uog_required_choices'))->create(['maximum_students' => 1]);
        $uestcProjects = factory(Project::class, config('projects.uestc_required_choices'))->create(['maximum_students' => 1, 'institution' => 'UESTC']);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $uestcProjects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $otherStudent = factory(User::class)->states('student')->create();
        $firstProject = Project::first();
        $firstProject->acceptStudent($otherStudent);

        $projectIds = $projects->pluck('id')->toArray();
        $uestcProjectIds = $uestcProjects->pluck('id')->toArray();
        $response = $this->actingAs($student)
            ->post(route('choices.update'), [
                'uestcChoices' => implode(',', $uestcProjectIds),
                'uogChoices' => implode(',', $projectIds),
            ]);

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
        $projects = factory(Project::class, config('projects.uog_required_choices'))->create(['maximum_students' => 1]);
        $projects->each(function ($project, $key) use ($course) {
            $project->courses()->save($course);
        });
        $projectIds = $projects->pluck('id')->toArray();

        ProjectConfig::setOption('applications_allowed', 0);
        $response = $this->actingAs($student)
            ->post(route('choices.update', ['uogchoices' => $projectIds]));

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
        $projects = factory(Project::class, config('projects.uog_required_choices'))->create(['maximum_students' => 1]);
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

    /** @test */
    public function a_student_can_post_their_project_preferences()
    {
        $student = $this->createStudent(['degree_type' => 'Dual']);
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        list($uogProject1, $uogProject2, $uogProject3) = factory(\App\Project::class, 3)->create(['institution' => 'UoG'])
            ->each(function ($project) use ($course) {
                $project->courses()->sync([$course->id]);
            });
        list($uestcProject1, $uestcProject2, $uestcProject3, $uestcProject4, $uestcProject5, $uestcProject6) = factory(\App\Project::class, 6)->create(['institution' => 'UESTC'])
            ->each(function ($project) use ($course) {
                $project->courses()->sync([$course->id]);
            });

        $this->actingAs($student)->post(route('choices.update'), [
            'uogChoices' => '2, 3, 1',
            'uestcChoices' => '5, 4, 9, 6, 8, 7',
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 1,
            'preference' => 3,
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 2,
            'preference' => 1,
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 3,
            'preference' => 2,
        ]);

        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 4,
            'preference' => 2,
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 5,
            'preference' => 1,
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 6,
            'preference' => 4,
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 7,
            'preference' => 6,
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 8,
            'preference' => 5,
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => 9,
            'preference' => 3,
        ]);
    }
}
