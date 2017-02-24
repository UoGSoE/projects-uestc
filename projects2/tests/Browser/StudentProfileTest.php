<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StudentProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_a_member_of_staff_can_view_a_students_profile()
    {
        $student = $this->createStudent();
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $student->storeCV($file);
        $student->save();
        $staff = $this->createStaff();
        $project = $this->createProject(['user_id' => $staff->id]);
        $project->addStudent($student);

        $this->browse(function ($browser) use ($staff, $student, $project) {
            $browser->loginAs($staff)
                    ->visit(route('project.show', $project->id))
                    ->assertSee($project->title)
                    ->assertSee($student->fullName())
                    ->assertSee($student->matric())
                    ->clickLink($student->matric())
                    ->assertSee($student->bio)
                    ->assertSee('Download their CV');
        });
    }

    private function createStudent()
    {
        return factory(\App\User::class)->states('student')->create(['bio' => 'NSNSNSNSNSNSNSNS']);
    }

    private function createStaff()
    {
        return factory(\App\User::class)->states('staff')->create();
    }

    private function createProject($attribs = [])
    {
        return factory(\App\Project::class)->create($attribs);
    }
}
