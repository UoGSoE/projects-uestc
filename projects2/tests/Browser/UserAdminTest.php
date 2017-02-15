<?php

namespace Tests\Browser;

use App\User;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserAdminTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_staff_admin_routes_cant_be_accessed_by_regular_users()
    {
        $regularUser = factory(User::class)->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)
                        ->get(route('staff.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_a_new_staff_member()
    {
        $adminUser = factory(User::class)->create(['is_admin' => true]);
        $this->browse(function ($browser) use ($adminUser) {
            $browser->loginAs($adminUser)
                    ->visit(route('staff.index'))
                    ->assertDontSee('HELLOKITTY')
                    ->clickLink('Add New Staff')
                    ->assertSee('New User')
                    ->type('username', 'HELLOKITTY')
                    ->type('surname', 'Kitty')
                    ->type('forenames', 'Hello')
                    ->type('email', 'hello@kitty.com')
                    ->press('Create')
                    ->assertSee('Details for')
                    ->assertSee('staff')
                    ->assertSee('HELLOKITTY');
        });
    }

    public function test_admin_can_create_a_new_student_member()
    {
        $adminUser = factory(User::class)->create(['is_admin' => true]);
        $this->browse(function ($browser) use ($adminUser) {
            $browser->loginAs($adminUser)
                    ->visit(route('student.index'))
                    ->assertDontSee('HELLOKITTY')
                    ->clickLink('Add New Student')
                    ->assertSee('New User')
                    ->type('username', 'HELLOKITTY')
                    ->type('surname', 'Kitty')
                    ->type('forenames', 'Hello')
                    ->type('email', 'hello@kitty.com')
                    ->check('is_student')
                    ->press('Create')
                    ->assertSee('Details for')
                    ->assertSee('student')
                    ->assertSee('HELLOKITTY');
        });
    }

    public function test_admin_can_edit_an_existing_user()
    {
        $adminUser = factory(User::class)->create(['is_admin' => true]);
        $staffUser = factory(User::class)->create(['is_student' => false]);
        $this->browse(function ($browser) use ($adminUser, $staffUser) {
            $browser->loginAs($adminUser)
                    ->visit(route('staff.index'))
                    ->clickLink($staffUser->username)
                    ->clickLink('Edit')
                    ->type('username', 'SOMEOTHERNAME')
                    ->press('Update')
                    ->assertSee('Details for')
                    ->assertSee('SOMEOTHERNAME');
        });
    }

    public function test_admin_can_impersonate_another_user()
    {
        $adminUser = factory(User::class)->create(['is_admin' => true]);
        $staffUser = factory(User::class)->create(['is_student' => false]);
        $this->browse(function ($browser) use ($adminUser, $staffUser) {
            $browser->loginAs($adminUser)
                    ->assertSee("Log Out {$adminUser->fullName()}")
                    ->visit(route('user.show', $staffUser->id))
                    ->clickLink('Log in as')
                    ->assertSee("Log Out {$staffUser->fullName()}");
        });
    }
}
