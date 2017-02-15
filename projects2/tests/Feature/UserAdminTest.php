<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;

class UserAdminTest extends TestCase
{
    use DatabaseMigrations;

    public function test_staff_admin_routes_cant_be_accessed_by_regular_users()
    {
        $regularUser = factory(User::class)->create(['is_admin' => false]);

        $response = $this->actingAs($regularUser)
                        ->get(route('staff.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_current_staff()
    {
        $adminUser = factory(User::class)->create(['is_admin' => true]);
        $regularUser = factory(User::class)->create(['is_admin' => false]);

        $response = $this->actingAs($adminUser)
                        ->get(route('staff.index'));

        $response->assertStatus(200);
        $response->assertSee('Current Staff');
        $response->assertSee($adminUser->username);
        $response->assertSee($regularUser->username);
    }

    public function test_admin_can_create_a_new_user()
    {
        $adminUser = factory(User::class)->create(['is_admin' => true]);

        $response = $this->actingAs($adminUser)
                        ->get(route('staff.index'));
        $response->assertDontSee('HELLOKITTY');

        $response = $this->actingAs($adminUser)
                        ->post(route('user.store'), [
                            'username' => 'HELLOKITTY',
                            'surname' => 'Kitty',
                            'forenames' => 'Hello',
                            'is_student' => false,
                            'email' => 'hellokitty@example.com'
                        ]);
        $response->assertStatus(302);

        $response = $this->actingAs($adminUser)
                        ->get(route('staff.index'));
        $response->assertSee('HELLOKITTY');

    }
}
