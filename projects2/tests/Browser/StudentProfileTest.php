<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\ProjectConfig;

class StudentProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function students_can_edit_their_profile()
    {
        $student = $this->createStudent();

        $this->browse(function ($browser) use ($student) {
            $browser->loginAs($student)
                    ->visit('/')
                    ->clickLink('Edit my profile')
                    ->assertSee('Your Profile')
                    ->type('bio', 'MY THRILLING BIO')
                    ->attach('cv', 'tests/data/test_cv.pdf')
                    ->press('Update')
                    ->assertPathIs('/')
                    ->assertSee('Profile Updated')
                    ->clickLink('Edit my profile')
                    ->assertSee('MY THRILLING BIO');
        });
    }

    /** @test */
    public function staff_can_view_the_profile_of_a_student_who_has_applied_for_their_project()
    {
        ProjectConfig::setOption('round', 1);
        $student = $this->createStudent();
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $student->storeCV($file);
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
                    ->clickLink('Download their CV');
        });
    }
}
