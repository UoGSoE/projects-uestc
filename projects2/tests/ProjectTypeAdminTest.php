<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ProjectTypeAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function testTypeList()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/projecttype')
            ->see($this->type->title);
    }

    public function testCreateNewType()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/projecttype')
            ->click('New Type')
            ->see('Create a new')
            ->type('QPQPQPQPQPQ', 'title')
            ->press('Create')
            ->see('Project Types')
            ->see('QPQPQPQPQPQ');
    }

    public function testEditAType()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/projecttype')
            ->click($this->type->title)
            ->see('Edit Project Type')
            ->type('ZZZZZZZZZ', 'title')
            ->press('Update')
            ->see('Project Types')
            ->see('ZZZZZZZZZ')
            ->dontSee($this->type->title);
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
