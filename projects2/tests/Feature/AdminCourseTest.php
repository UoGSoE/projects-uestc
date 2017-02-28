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

}
