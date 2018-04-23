<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StudentChooseProjectsTest extends DuskTestCase
{

    /** @test */
    public function if_student_hasnt_selected_degree_type_then_redirect_them_to_choose ()
    {
        $student = $this->createStudent(['degree_type' => null]);
        $this->browse(function ($browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/')
                    ->assertDontSee('Available Projects')
                    ->assertSee('Degree Type');
        });
    }
    /** @test */
    public function a_student_can_see_available_projects()
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        $discipline = $this->createDiscipline();
        list($project1, $project2, $project3) = factory(\App\Project::class, 3)->create()->each(function ($project) use ($course) {
            $project->courses()->sync([$course->id]);
        });
        $project1->discipline_id = $discipline->id;
        $project1->save();
        $disabledProject = $this->createProject(['is_active' => false]);
        $fullProject = $this->createProject(['maximum_students' => 0]);

        $this->browse(function ($browser) use ($student, $project1, $project2, $project3, $disabledProject, $fullProject) {
            $browser->loginAs($student)
                    ->visit('/')
                    ->assertSee('Available Projects')
                    ->assertSee($project1->title)
                    ->assertSee($project1->disciplineTitle())
                    ->assertSee($project2->title)
                    ->assertSee($project3->title)
                    ->assertDontSee($disabledProject->title)
                    ->assertDontSee($fullProject->title);
        });
    }

    /** @test */
    public function a_student_can_see_popularity_of_projects()
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $student2 = $this->createStudent();
        $student3 = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        $discipline = $this->createDiscipline();
        list($project1, $project2, $project3) = factory(\App\Project::class, 3)->create()->each(function ($project) use ($course, $student2, $student3) {
            $project->courses()->sync([$course->id]);
            $project->students()->sync([$student2->id, $student3->id]);
        });
        $fullProject = $this->createProject(['maximum_students' => 0]);

        $this->browse(function ($browser) use ($student, $project1, $project2, $project3, $fullProject) {
            $browser->loginAs($student)
                    ->visit('/')
                    ->assertSee('Available Projects')
                    ->assertSee($project1->title)
                    ->assertSee($project1->disciplineTitle())
                    ->click("#title_{$project1->id}")
                    ->assertSee("Somewhat popular")
                    ->assertSee($project2->title)
                    ->assertSee($project3->title)
                    ->assertDontSee($fullProject->title);
        });
    }

    //Test doesnt work as Dusk can't see all elements on the screen.
    // /** @test */
    // public function a_student_can_only_pick_configured_maximum_of_projects()
    // {
    //     $staff = $this->createStaff();
    //     $student = $this->createStudent();
    //     $course = $this->createCourse();
    //     $course->students()->sync([$student->id]);
    //     $discipline = $this->createDiscipline();
    //     list($uogProject1, $uogProject2, $uogProject3, $uogProject4) = factory(\App\Project::class, 4)->create()->each(function ($uogProject) use ($course) {
    //         $uogProject->courses()->sync([$course->id]);
    //     });
    //     list($uestcProject1, $uestcProject2, $uestcProject3, $uestcProject4, $uestcProject5, $uestcProject6, $uestcProject7) = factory(\App\Project::class, 7)->create(['institution' => 'UESTC'])->each(function ($uestcProject) use ($course) {
    //         $uestcProject->courses()->sync([$course->id]);
    //     });

    //     $this->browse(function ($browser) use ($student, $uogProject1, $uogProject2, $uogProject3, $uogProject4, $uestcProject1, $uestcProject2, $uestcProject3, $uestcProject4, $uestcProject5, $uestcProject6, $uestcProject7) {
    //         $browser->loginAs($student)
    //                 ->visit('/')
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uogProject1->id}")
    //                 ->check("#choose_{$uogProject1->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uogProject2->id}")
    //                 ->check("#choose_{$uogProject2->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uogProject3->id}")
    //                 ->check("#choose_{$uogProject3->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uogProject4->id}")
    //                 ->check("#choose_{$uogProject4->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uestcProject1->id}")
    //                 ->check("#choose_{$uestcProject1->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uestcProject2->id}")
    //                 ->check("#choose_{$uestcProject2->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uestcProject3->id}")
    //                 ->check("#choose_{$uestcProject3->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uestcProject4->id}")
    //                 ->check("#choose_{$uestcProject4->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uestcProject5->id}")
    //                 ->check("#choose_{$uestcProject5->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uestcProject6->id}")
    //                 ->check("#choose_{$uestcProject6->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->click("#title_{$uestcProject7->id}")
    //                 ->check("#choose_{$uestcProject7->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->uncheck("#choose_{$uestcProject7->id}")
    //                 ->assertDontSee('Submit your choices')
    //                 ->uncheck("#choose_{$uogProject4->id}")
    //                 ->assertSee('Submit your choices');
    //     });
    // }

    /** @test */
    public function a_student_can_see_links_and_files_associated_with_a_project()
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        $discipline = $this->createDiscipline();
        $project = factory(\App\Project::class)->create();
        $project->courses()->sync([$course->id]);
        $project->syncLinks([['url' => 'http://www.example.com'], ['url' => 'http://www.blah.com']]);
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $files = [$file];
        $project->addFiles($files);

        $this->browse(function ($browser) use ($student, $project) {
            $browser->loginAs($student)
                    ->visit('/')
                    ->click("#title_{$project->id}")
                    ->assertSee('http://www.example.com')
                    ->assertSee('http://www.blah.com')
                    ->assertSee('test_cv.pdf');
        });
    }
}
