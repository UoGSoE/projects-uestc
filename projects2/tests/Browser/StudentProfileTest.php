<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class StudentProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_a_member_of_staff_can_download_a_students_cv()
    {
        $student = $this->createStudent();
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $student->storeCV($file);
        $student->save();
        $staff = $this->createStaff();

        $this->browse(function ($browser) use ($staff, $student) {
            $browser->loginAs($staff)
                    ->visit(route('student.cv', $student->id))
                    ->assertSee('Laravel');
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
}
