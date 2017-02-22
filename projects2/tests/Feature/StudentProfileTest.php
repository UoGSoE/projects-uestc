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

    public function test_staff_can_view_a_students_profile()
    {
        $student = $this->createStudent();
        $staff = $this->createStaff();

        $response = $this->actingAs($staff)->get(route('student.profile_show', $student->id));

        $response->assertStatus(200);
    }

    private function createStudent()
    {
        return factory(\App\User::class)->states('student')->create(['bio' => 'NSNSNSNSNSNSNSNS']);
    }

    private function createStaff()
    {
        return factory(\App\User::class)->states('staff')->create(['bio' => 'NSNSNSNSNSNSNSNS']);
    }
}
