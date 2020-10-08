<?php

// @codingStandardsIgnoreFile

namespace Tests\Browser;

use App\ProjectConfig;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class StaffProjectTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function staff_can_create_a_new_project()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $disciplines = \App\Discipline::factory()->count(5)->create();

        $this->browse(function ($browser) use ($staff, $course, $disciplines) {
            $browser->loginAs($staff)
                    ->visit('/')
                    ->assertSee('Your Projects')
                    ->clickLink('New Project')
                    ->assertSee('Create A New Project')
                    ->type('title', 'MY AMAZING PROJECT')
                    ->type('description', 'AN AMAZING DESCRIPTION')
                    ->type('prereq', 'AMAZING PREREQS')
                    ->check('is_active')
                    ->select('#inputCourses', "$course->id")
                    ->select('#inputDisc', "{$disciplines->first()->id}")
                    ->select('#inputDisc', "{$disciplines->get(1)->id}")
                    ->attach('files[]', 'tests/data/test_cv.pdf')
                    ->press('Create')
                    ->assertSee('Project Details')
                    ->assertSee('MY AMAZING PROJECT')
                    ->assertSee('AN AMAZING DESCRIPTION')
                    ->assertSee('AMAZING PREREQS')
                    ->assertSee($disciplines->first()->title)
                    ->assertSee($disciplines->get(1)->title)
                    ->assertSee($course->title)
                    ->assertSee('test_cv.pdf');
        });
    }

    /** @test */
    public function staff_can_edit_an_existing_project()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $disciplines = \App\Discipline::factory()->count(5)->create();
        $project = $this->createProject(['user_id' => $staff->id]);
        $project->courses()->sync([$course->id]);
        $this->browse(function ($browser) use ($staff, $course, $disciplines, $project) {
            $file = $this->createProjectFile(['project_id' => $project->id]);
            $link = $this->createProjectLink(['project_id' => $project->id]);
            $browser->loginAs($staff)
                    ->visit('/')
                    ->assertSee('Your Projects')
                    ->clickLink($project->title)
                    ->assertSeeIn('#is_active', 'Yes')
                    ->clickLink('Edit')
                    ->assertSee('Edit Project')
                    ->assertSee($file->original_filename)
                    ->assertSee($link->url)
                    ->type('title', 'MY AMAZING PROJECT')
                    ->type('description', 'AN AMAZING DESCRIPTION')
                    ->type('prereq', 'AMAZING PREREQS')
                    ->uncheck('is_active')
                    ->check("deletefiles[{$file->id}]")
                    ->check("deletelinks[{$link->id}]")
                    ->attach('files[]', 'tests/data/test_cv.pdf')
                    ->type('links[][url]', 'http://www.veryunlikelytoexist.com')
                    ->press('Update')
                    ->assertSee('Project Details')
                    ->assertSee('MY AMAZING PROJECT')
                    ->assertSee('AN AMAZING DESCRIPTION')
                    ->assertSee('AMAZING PREREQS')
                    ->assertSeeIn('#is_active', 'No')
                    ->assertDontSee($file->original_filename)
                    ->assertDontSee($link->url)
                    ->assertSee('test_cv.pdf')
                    ->assertSee('http://www.veryunlikelytoexist.com');
        });
    }

    //Functionality was removed as staff are no longer allowed to pick their students
    // /** @test */
    // public function staff_can_accept_a_student_onto_project()
    // {
    //     ProjectConfig::setOption('round', 1);
    //     $staff = $this->createStaff();
    //     $course = $this->createCourse();
    //     $student = $this->createStudent();
    //     $student2 = $this->createStudent();
    //     $disciplines = \App\Discipline::factory()->count(5)->create();
    //     $project = $this->createProject(['user_id' => $staff->id, 'maximum_students' => 1]);
    //     $project->courses()->sync([$course->id]);
    //     $project->addStudent($student);
    //     $project->addStudent($student2);

    //     $this->browse(function ($browser) use ($staff, $project, $student, $student2) {
    //         $browser->loginAs($staff)
    //                 ->visit('/')
    //                 ->assertSee('Your Projects')
    //                 ->clickLink($project->title)
    //                 ->assertSee($student->fullName())
    //                 ->assertSee($student2->fullName())
    //                 ->radio("accepted", $student->id)
    //                 ->press('Allocate')
    //                 ->assertSee('Allocations Saved')
    //                 ->assertSee($student->fullName())
    //                 ->assertDontSee($student2->fullName())
    //                 ->assertDontSee('Allocate');
    //     });
    //     $this->assertDatabaseHas('project_student', [
    //         'user_id' => $student->id,
    //         'project_id' => $project->id,
    //         'accepted' => true
    //     ]);
    // }

    /* @test */
    /*.
        Not needed at present as form input changed from checkboxes (when staff could
        allocate more than one student to a project) to a radio so only one is ever (ha)
        possible to submit.

    public function staff_cant_accept_more_than_maximum_allowed_students_onto_project()
    {
        $staff = $this->createStaff();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $student2 = $this->createStudent();
        $disciplines = \App\Discipline::factory()->count(5)->create();
        $project = $this->createProject(['user_id' => $staff->id, 'maximum_students' => 1]);
        $project->courses()->sync([$course->id]);
        $project->addStudent($student);
        $project->addStudent($student2);

        $this->browse(function ($browser) use ($staff, $project, $student, $student2) {
            $browser->loginAs($staff)
                    ->visit('/')
                    ->assertSee('Your Projects')
                    ->clickLink($project->title)
                    ->assertSee($student->fullName())
                    ->check("accepted[{$student->id}]")
                    ->check("accepted[{$student2->id}]")
                    ->press('Allocate')
                    ->assertSee('You cannot accept more then 1 student onto the project');
        });
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student->id,
            'project_id' => $project->id,
            'accepted' => false
        ]);
        $this->assertDatabaseHas('project_student', [
            'user_id' => $student2->id,
            'project_id' => $project->id,
            'accepted' => false
        ]);
    }*/
}
