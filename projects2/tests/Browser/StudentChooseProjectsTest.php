<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StudentChooseProjectsTest extends DuskTestCase
{
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
        $disabledProject = $this->createProject(['is_active' => false]);
        $fullProject = $this->createProject(['maximum_students' => 0]);

        $this->browse(function ($browser) use ($student, $project1, $project2, $project3, $disabledProject, $fullProject) {
            $browser->loginAs($student)
                    ->visit('/')
                    ->assertSee('Available Projects')
                    ->assertSee($project1->title)
                    ->assertSee($project2->title)
                    ->assertSee($project3->title)
                    ->assertDontSee($disabledProject->title)
                    ->assertDontSee($fullProject->title);
        });
    }

    /** @test */
    public function a_student_can_only_pick_configured_maximum_of_projects()
    {
        $staff = $this->createStaff();
        $student = $this->createStudent();
        $course = $this->createCourse();
        $course->students()->sync([$student->id]);
        $discipline = $this->createDiscipline();
        list($project1, $project2, $project3, $project4) = factory(\App\Project::class, 4)->create()->each(function ($project) use ($course) {
            $project->courses()->sync([$course->id]);
        });

        $this->browse(function ($browser) use ($student, $project1, $project2, $project3, $project4) {
            $browser->loginAs($student)
                    ->visit('/')
                    ->assertDontSee('Submit your choices')
                    ->click("#title_{$project1->id}")
                    ->check("#choose_{$project1->id}")
                    ->assertDontSee('Submit your choices')
                    ->click("#title_{$project2->id}")
                    ->check("#choose_{$project2->id}")
                    ->assertDontSee('Submit your choices')
                    ->click("#title_{$project3->id}")
                    ->check("#choose_{$project3->id}")
                    ->assertSee('Submit your choices')
                    ->click("#title_{$project4->id}")
                    ->check("#choose_{$project4->id}")
                    ->assertDontSee('Submit your choices');
        });
    }

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
