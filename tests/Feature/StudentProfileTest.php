<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
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
        config(['projects.default_disk' => 'local']);
        Storage::fake('local');

        $student = $this->createStudent();
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $response = $this->actingAs($student)
                        ->call('POST', route('student.profile_update'), [], [], ['cv' => $file]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('users', ['id' => $student->id, 'cv_file' => "{$student->id}_cv.pdf"]);
        Storage::disk(config('projects.default_disk'))->assertExists("cvs/{$student->id}_cv.pdf");
        $student->deleteCV();   // just to tidy up after the test so we don't leave artifacts around
    }

    public function test_deleting_a_cv_does_remove_the_file_and_db_entry()
    {
        config(['projects.default_disk' => 'local']);
        Storage::fake('local');

        $student = $this->createStudent();
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $student->storeCV($file);

        Storage::disk(config('projects.default_disk'))->assertExists("cvs/{$student->id}_cv.pdf");
        $this->assertEquals("{$student->id}_cv.pdf", $student->fresh()->cv_file);

        $student->deleteCV();

        Storage::disk(config('projects.default_disk'))->assertMissing("cvs/{$student->id}_cv.pdf");
        $this->assertNull($student->fresh()->cv_file);
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

    public function test_student_can_set_their_degree_type()
    {
        $student = $this->createStudent();
        $response = $this->actingAs($student)->post(route('student.profile_update'), ['degree_type' => 'Single']);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success_message');
        $this->assertDatabaseHas('users', ['id' => $student->id, 'degree_type' => 'Single']);
    }

    public function test_staff_can_download_a_students_cv()
    {
        config(['projects.default_disk' => 'local']);
        Storage::fake(config('projects.default_disk'));
        $student = $this->createStudent();
        $staff = $this->createStaff();
        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $student->storeCV($file);

        $response = $this->actingAs($staff)->get(route('student.cv', $student->id));

        $response->assertStatus(200);
        // $response->dumpHeaders();
        $response->assertHeader('content-disposition', 'attachment; filename=' . $student->cv_file);
    }
}
