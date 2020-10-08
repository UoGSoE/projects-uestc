<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminCourseTest extends DuskTestCase
{
    /** @test */
    public function admin_can_create_and_edit_courses()
    {
        $admin = $this->createAdmin();

        $this->browse(function ($browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/')
                    ->clickLink('Admin')
                    ->clickLink('Courses')
                    ->clickLink('New Course')
                    ->type('title', 'MY THRILLING TITLE')
                    ->type('code', 'TEST9999')
                    ->press('Create')
                    ->assertSee('Course Saved')
                    ->clickLink('TEST9999')
                    ->assertSee('Course TEST9999')
                    ->clickLink('Edit')
                    ->type('title', 'MY UPDATED TITLE')
                    ->type('code', 'TEST1111')
                    ->press('Update')
                    ->assertSee('Course Saved')
                    ->assertSee('MY UPDATED TITLE')
                    ->assertSee('TEST1111')
                    ->clickLink('TEST1111')
                    ->clickLink('Edit')
                    ->clickLink('Delete')
                    ->assertSee('Course Deleted')
                    ->assertDontSee('TEST1111');
        });
    }

    /** @test */
    public function admin_can_import_and_remove_students_from_a_course()
    {
        $admin = $this->createAdmin();
        $course = $this->createCourse();

        $this->browse(function ($browser) use ($admin, $course) {
            $browser->loginAs($admin)
                    ->visit(route('course.show', $course->id))
                    ->clickLink('Import')
                    ->attach('file', 'tests/data/test_student.xlsx')
                    ->press('Import')
                    ->assertSee('SURNAME1')
                    ->assertSee('NAMESUR2')
                    ->clickLink('Remove All Students')
                    ->waitFor('#dataConfirmModal')
                    ->press('Cancel')
                    ->assertSee('SURNAME1')
                    ->clickLink('Remove All Students')
                    ->waitFor('#dataConfirmModal')
                    ->clickLink('OK')
                    ->assertDontSee('SURNAME1')
                    ->assertDontSee('NAMESUR1');
        });
    }
}
