<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminCourseTest extends TestCase
{
    /** @test */
    public function admins_can_see_the_list_of_courses()
    {
        $admin = $this->createAdmin();
        $course1 = $this->createCourse();
        $course2 = $this->createCourse();

        $response = $this->actingAs($admin)->get(route('course.index'));

        $response->assertStatus(200);
        $response->assertSee('Courses');
        $response->assertSee($course1->title);
        $response->assertSee($course2->title);
    }

    /** @test */
    public function admin_can_create_a_course()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('course.store'), [
            'title' => 'AN AMAZING COURSE',
            'code' => 'TEST1234'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.index'));
        $this->assertDatabaseHas('courses', ['title' => 'AN AMAZING COURSE', 'code' => 'TEST1234']);
    }

    /** @test */
    public function admin_can_edit_a_course()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();

        $response = $this->actingAs($admin)->post(route('course.update', $course->id), [
            'title' => 'AN UPDATED COURSE',
            'code' => 'TEST5678'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.index'));
        $this->assertDatabaseHas('courses', ['title' => 'AN UPDATED COURSE', 'code' => 'TEST5678']);
        $this->assertDatabaseMissing('courses', ['title' => $course->title, 'code' => $course->code]);
    }

    /** @test */
    public function admin_can_delete_a_course()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $course2 = $this->createCourse();
        $project = $this->createProject();
        $project->courses()->sync([$course->id, $course2->id]);

        $response = $this->actingAs($admin)->delete(route('course.destroy', $course->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('course.index'));
        $this->assertDatabaseMissing('courses', ['title' => $course->title, 'code' => $course->code]);
        $this->assertDatabaseMissing('course_project', ['project_id' => $project->id, 'course_id' => $course->id]);
        $this->assertDatabaseHas('course_project', ['project_id' => $project->id, 'course_id' => $course2->id]);
    }

    /** @test */
    public function admin_can_remove_all_students_from_a_course()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $course2 = $this->createCourse();
        $project = $this->createProject();
        $project->courses()->sync([$course->id, $course2->id]);
        $student = $this->createStudent();
        $student->projects()->sync([$project->id]);
        $student->courses()->sync([$course->id]);

        $response = $this->actingAs($admin)->post(route('enrol.destroy', $course->id));

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $course->id));
        $this->assertDatabaseMissing('course_student', ['user_id' => $student->id, 'course_id' => $course->id]);
        $this->assertDatabaseMissing('project_student', ['user_id' => $student->id, 'project_id' => $project->id]);
    }

    /** @test */
    public function admin_can_import_a_spreadsheet_of_students_onto_a_course()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $filename = 'tests/data/test_student.xlsx';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_student.xlsx', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);

        $response = $this->actingAs($admin)
                        ->call('POST', route('enrol.update', $course->id), [], [], ['file' => $file]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $course->id));
        $student1 = \App\User::orderBy('id')->students()->first();
        $student2 = \App\User::orderBy('id', 'desc')->students()->first();
        $this->assertDatabaseHas('course_student', ['course_id' => $course->id, 'user_id' => $student1->id]);
        $this->assertDatabaseHas('course_student', ['course_id' => $course->id, 'user_id' => $student2->id]);
        $this->assertDatabaseHas('users', ['username' => '1234567s', 'surname' => 'SURNAME1']);
        $this->assertDatabaseHas('users', ['username' => '7654321n', 'surname' => 'NAMESUR2']);
    }

    /** @test */
    public function importing_students_onto_a_course_removes_any_existing_ones()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();
        $student = $this->createStudent();
        $student->courses()->sync([$course->id]);
        $filename = 'tests/data/test_student.xlsx';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_student.xlsx', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);

        $response = $this->actingAs($admin)
                        ->call('POST', route('enrol.update', $course->id), [], [], ['file' => $file]);

        $response->assertStatus(302);
        $response->assertRedirect(route('course.show', $course->id));
        $student1 = \App\User::orderBy('id')->students()->first();
        $student2 = \App\User::orderBy('id', 'desc')->students()->first();
        $this->assertDatabaseMissing('course_student', ['course_id' => $course->id, 'user_id' => $student->id]);
    }
}
