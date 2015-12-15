<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserAdminTest extends TestCase
{
    use DatabaseTransactions;

    public function testCanCreateUsers()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/user/staff')
            ->click('Add New Staff')
            ->see('New User')
            ->type('PQPQPQPQP', 'username')
            ->type('MMMMMMMMM', 'surname')
            ->type('ZZZZZZZZZ', 'forenames')
            ->type('AAAAA@bbbbb.com', 'email')
            ->press('Create')
            ->see("Details for ZZZZZZZZZ MMMMMMMMM")
            ->see('aaaaa@bbbbb.com')   // double-check emails are forced to lower case
            ->see('Internal Staff');
        $this->actingAs($this->staff)
            ->visit('/user/students')
            ->click('Add New Student')
            ->see('New User')
            ->type('QQQQQQQQQ', 'username')
            ->type('MMMMMMMMM', 'surname')
            ->type('QQQQQQQQQ', 'forenames')
            ->type('ZZZZZ@bbbbb.com', 'email')
            ->check('is_student')
            ->type('abcd1234abcd1234', 'password')
            ->press('Create')
            ->see("Details for QQQQQQQQQ MMMMMMMMM")
            ->see('zzzzz@bbbbb.com')
            ->see('External Student');
    }

    public function testCanEditUser()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit("/user/{$this->staff2->id}/edit")
            ->see('Edit ')
            ->see($this->staff2->fullName())
            ->type('12345abcdQP', 'username')
            ->press('Update')
            ->see('Details for')
            ->see('12345abcdQP');
    }

    public function testAddAndRemoveRolesFromUser()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit("/user/{$this->staff2->id}/edit")
            ->submitForm([
                'roles' => [$this->role->id]
            ])
            ->see('Details for')
            ->see($this->role->label)
            ->visit("/user/{$this->staff2->id}/edit")
            ->submitForm([
                'roles' => []
            ])
            ->see('Details for')
            ->dontSee($this->role->label);
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
