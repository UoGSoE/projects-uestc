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
                    ->press('Delete')
                    ->assertSee('Course Deleted')
                    ->assertDontSee('TEST1111');
        });
    }
}
