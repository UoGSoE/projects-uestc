<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class CourseAdminTest extends DuskTestCase
{
    public $staff;

    public function test_nothing()
    {
        $this->assertTrue(true);
    }

    /*
        public function test_can_see_a_list_of_courses()
        {
            $this->buildWorld();
            $this->browse(function ($browser) {
                $browser->loginAs($this->staff)
                        ->visit('/')
                        ->clickLink('Courses')
                        ->assertSee($this->course->code)
                        ->assertSee($this->course->title);
            });
        }

        public function test_can_create_a_course()
        {
            $this->buildWorld();
            $this->browse(function ($browser) {
                $browser->loginAs($this->staff)
                        ->visit('/course')
                        ->clickLink('New Course')
                        ->type('code', 'ZZZZ1239999')
                        ->type('title', 'LALALALALALALALA')
                        ->press('Create')
                        ->assertSee('ZZZZ1239999')
                        ->assertSee('LALALALALALALALA')
                        ->assertSee('Students');
            });
        }

        public function test_can_edit_a_course()
        {
            $this->buildWorld();
            $this->browse(function ($browser) {
                $browser->loginAs($this->staff)
                    ->visit('/course')
                    ->clickLink($this->course->code)
                    ->assertSee("Course {$this->course->code}")
                    ->clickLink("Edit")
                    ->assertSee('Edit Course')
                    ->type('code', 'ALALALAL9191')
                    ->press('Update')
                    ->assertSee("Course ALALALAL9191")
                    ->assertDontSee($this->course->code)
                    ->assertSee('Students');
            });
        }
    /*
        public function testCantAddDuplicateCode()
        {
            $this->buildWorld();
            $this->actingAs($this->staff)
                ->visit('/course')
                ->click('New Course')
                ->see('New Course')
                ->type($this->course->code, 'code')
                ->type('LALALALALALALALA', 'title')
                ->press('Create')
                ->see('The code has already been taken');
        }

        public function testUploadStudentSpreadsheet()
        {
            $this->buildWorld();
            $spreadsheet = 'tests/student_test.xlsx';
            $this->actingAs($this->staff)
                ->visit("/course/{$this->course->id}")
                ->click('Import')
                ->see("Import students on")
                ->attach($spreadsheet, 'file')
                ->press('Import')
                ->see("Course {$this->course->code}")
                ->see('Theres Been Taggart');   // a name from the test spreadsheet
            // now test uploading the same students to another course removes them from the first
            $course2 = \App\Models\Course::factory()->create();
            $this->actingAs($this->staff)
                ->visit("/course/{$course2->id}")
                ->click('Import')
                ->see("Import students on")
                ->attach($spreadsheet, 'file')
                ->press('Import')
                ->see("Course {$course2->code}")
                ->see('Theres Been Taggart')
                ->visit("/course/{$this->course->id}")
                ->dontSee('Theres Been Taggart');
        }
    */
    private function buildWorld()
    {
        $this->staff = \App\Models\User::factory()->create(['is_student' => false]);
        $this->staff2 = \App\Models\User::factory()->create(['is_student' => false]);
        $this->student = \App\Models\User::factory()->create(['is_student' => true]);
        $this->student2 = \App\Models\User::factory()->create(['is_student' => true]);
        $this->course = \App\Models\Course::factory()->create();
        $this->type = \App\ProjectType::factory()->create();
        $this->project = \App\Models\Project::factory()->create([
            'user_id' => $this->staff2->id, 'type_id' => $this->type->id,
        ]);
        $this->role = \App\Role::factory()->create(['title' => 'site_admin']);
        $this->permission = \App\Permission::factory()->create(['title' => 'edit_users']);
        $this->permission2 = \App\Permission::factory()->create(['title' => 'view_users']);
        $this->permission3 = \App\Permission::factory()->create(['title' => 'edit_courses']);
        $this->role->permissions()->sync([$this->permission->id, $this->permission2->id, $this->permission3->id]);
        $this->staff->roles()->sync([$this->role->id]);
    }
}
