<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StudentProfileTest extends TestCase
{
    public function test_a_student_can_see_their_bio_information()
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->get(route('student.profile_edit'));

        $response->assertStatus(200);
        $response->assertSee($student->fullName());
        $response->assertSee($student->bio);
        $response->assertSee('Update');
    }

    public function test_a_student_can_update_their_bio_information()
    {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->post(route('student.profile_update'), ['bio' => 'MYNEWBIO']);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('users', ['id' => $student->id, 'bio' => 'MYNEWBIO']);
    }

    public function test_a_student_can_upload_a_cv()
    {
        $student = $this->createStudent();
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $response = $this->actingAs($student)
                        ->call('POST', route('student.profile_update'), [], [], ['cv' => $file]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('users', ['id' => $student->id, 'cv_file' => "{$student->id}_cv.pdf"]);
        $student->deleteCV();   // just to tidy up after the test so we don't leave artifacts around
    }

    public function test_staff_can_view_a_students_profile()
    {
        $student = $this->createStudent();
        $student->cv_file = 'madeupcv.pdf';
        $student->save();
        $staff = $this->createStaff();

        $response = $this->actingAs($staff)->get(route('student.profile_show', $student->id));

        $response->assertStatus(200);
        $response->assertSee($student->bio);
        $response->assertSee('Download their CV');
    }

    // public function test_staff_can_download_a_students_cv()
    // {
    //     $student = $this->createStudent();
    //     $filename = 'tests/data/test_cv.pdf';
    //     $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
    //     $student->storeCV($file);
    //     $student->save();
    //     $staff = $this->createStaff();

    //     $response = $this->actingAs($staff)->get(route('student.cv', $student->id));

    //     $response->assertStatus(200);
    //     $response->assertHeader('attachment');
    // }

}
