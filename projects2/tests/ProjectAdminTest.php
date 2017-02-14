<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectAdminTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    public function testStudentList()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/report/students')
            ->see($this->student->fullName())
            ->see($this->student2->fullName());
    }

    public function testProjectList()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/report/projects')
            ->see($this->project1->title)
            ->see($this->project2->title)
            ->see($this->project3->title);
    }

    public function testStaffList()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/report/staff')
            ->see($this->staff->fullName())
            ->see($this->staff2->fullName());
    }

    public function testUploadStaffSpreadsheet()
    {
        $this->buildWorld();
        $spreadsheet = 'tests/staff_test.xlsx';
        $this->actingAs($this->staff)
            ->visit('/user/staff')
            ->click('Import Staff')
            ->see('Import a list of staff')
            ->attach($spreadsheet, 'file')
            ->press('Import')
            ->see('Updated staff list')
            ->see('Zippy')
            ->see('Bungle')
            ->see('xyz@gmail.com');   // a name from the test spreadsheet
    }

    public function testBulkAllocate()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/report/bulkallocate')
            ->see($this->student->fullName())
            ->see($this->student2->fullName())
            ->submitForm([
                'student' => [$this->student->id => $this->student->projects()->first()->id]
            ])
            ->press('Allocate Choices')
            ->dontSee($this->student->fullName());
    }

    public function testManualAllocation()
    {
        $this->buildWorld();
        $newproject = factory(App\Project::class)->create([
            'user_id' => $this->staff->id, 'type_id' => $this->type->id
        ]);
        $newproject->courses()->sync([$this->course->id]);
        $this->student->courses()->sync([$this->course->id]);
        $this->actingAs($this->staff)
            ->visit('/report/bulkallocate')
            ->see($this->student->fullName())
            ->visit("/user/{$this->student->id}/edit")
            ->see('Update')
            ->select($newproject->id, 'project_id')
            ->press('Update')
            ->see('Details for')
            ->see($newproject->title)
            ->visit('/report/bulkallocate')
            ->dontSee($this->student->fullName());
    }

    public function testManualDeAllocation()
    {
        $this->buildWorld();
        $this->project1->students()->sync([$this->student->id => ['accepted' => true]]);
        $this->actingAs($this->staff)
            ->visit("/project/{$this->project1->id}")
            ->see($this->student->fullName())
            ->seeIsChecked("accepted[{$this->student->id}]")
            ->uncheck("accepted[{$this->student->id}]")
            ->press('Allocate');
            // ->see('Allocations Saved')
            // ->dontSeeIsChecked("accepted[{$this->student->id}]");
    }

    public function testBulkActive()
    {
        $this->buildWorld();
        $this->project1->is_active = false;
        $this->project2->is_active = true;
        $this->project1->save();
        $this->project2->save();

        $this->actingAs($this->staff)
            ->visit("/project/bulkactive")
            ->see($this->project1->title)
            ->see($this->project2->title)
            ->seeIsSelected("statuses[{$this->project1->id}]", 0)
            ->seeIsSelected("statuses[{$this->project2->id}]", 1)
            ->select(1, "statuses[{$this->project1->id}]")
            ->select(0, "statuses[{$this->project2->id}]")
            ->press('Update')
            ->see($this->project1->title)
            ->see($this->project2->title)
            ->seeIsSelected("statuses[{$this->project1->id}]", 1)
            ->seeIsSelected("statuses[{$this->project2->id}]", 0);
    }

    private function buildWorld()
    {
        $this->staff = factory(App\User::class)->create(['is_student' => false]);
        $this->staff2 = factory(App\User::class)->create(['is_student' => false]);
        $this->student = factory(App\User::class)->create(['is_student' => true]);
        $this->student2 = factory(App\User::class)->create(['is_student' => true]);
        $this->course = factory(App\Course::class)->create();
        $this->type = factory(App\ProjectType::class)->create();
        $this->project1 = factory(App\Project::class)->create([
            'user_id' => $this->staff->id, 'type_id' => $this->type->id
        ]);
        $this->project2 = factory(App\Project::class)->create([
            'user_id' => $this->staff->id, 'type_id' => $this->type->id
        ]);
        $this->project3 = factory(App\Project::class)->create([
            'user_id' => $this->staff2->id, 'type_id' => $this->type->id
        ]);
        $this->project1->students()->sync([$this->student->id => ['accepted' => false, 'choice' => 1]]);
        $this->project2->students()->sync([
            $this->student2->id => ['accepted' => false, 'choice' => 2],
            $this->student->id => ['accepted' => false, 'choice' => 2]
        ]);
        $this->role = factory(App\Role::class)->create(['title' => 'site_admin']);
        $this->permission = factory(App\Permission::class)->create(['title' => 'edit_users']);
        $this->permission2 = factory(App\Permission::class)->create(['title' => 'view_users']);
        $this->role->permissions()->sync([$this->permission->id, $this->permission2->id]);
        $this->staff->roles()->sync([$this->role->id]);
    }
}
