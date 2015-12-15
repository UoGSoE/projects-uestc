<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CourseAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function testCourseList()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/course')
            ->see($this->course->title);
    }

    public function testCreateACourse()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/course')
            ->click('New Course')
            ->see('New Course')
            ->type('ZZZZ1239999', 'code')
            ->type('LALALALALALALALA', 'title')
            ->press('Create')
            ->see('ZZZZ1239999')
            ->see('LALALALALALALALA')
            ->see('Students');
    }

    public function testEditACourse()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/course')
            ->click($this->course->code)
            ->see("Course {$this->course->code}")
            ->visit("/course/{$this->course->id}/edit")
            ->see('Edit Course')
            ->type('ALALALAL9191', 'code')
            ->press('Update')
            ->see("Course ALALALAL9191")
            ->dontSee($this->course->code)
            ->see('Students');
    }

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

    private function buildWorld()
    {
        $this->staff = factory(App\User::class)->create(['is_student' => false]);
        $this->staff2 = factory(App\User::class)->create(['is_student' => false]);
        $this->student = factory(App\User::class)->create(['is_student' => true]);
        $this->student2 = factory(App\User::class)->create(['is_student' => true]);
        $this->course = factory(App\Course::class)->create();
        $this->type = factory(App\ProjectType::class)->create();
        $this->project = factory(App\Project::class)->create([
            'user_id' => $this->staff2->id, 'type_id' => $this->type->id
        ]);
        $this->role = factory(App\Role::class)->create(['title' => 'site_admin']);
        $this->permission = factory(App\Permission::class)->create(['title' => 'edit_users']);
        $this->permission2 = factory(App\Permission::class)->create(['title' => 'view_users']);
        $this->role->permissions()->sync([$this->permission->id, $this->permission2->id]);
        $this->staff->roles()->sync([$this->role->id]);
    }
}
