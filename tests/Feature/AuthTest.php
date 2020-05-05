<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    /** @test */
    public function a_valid_username_and_password_can_log_in()
    {
        $user = $this->createStaff(['password' => bcrypt('HELLOKITTY1234')]);

        $response = $this->post(route('login.login', ['username' => $user->username, 'password' => 'HELLOKITTY1234']));

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    /** @test */
    public function a_valid_email_and_password_can_log_in()
    {
        $user = $this->createStaff(['password' => bcrypt('HELLOKITTY1234')]);

        $response = $this->post(route('login.login', ['username' => $user->email, 'password' => 'HELLOKITTY1234']));

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    /** @test */
    public function an_invalid_password_fails()
    {
        if (env('CI')) {
            $this->markTestSkipped('Not doing ldap stuff in CI');
        }

        config(['ldap.authentication' => true]);
        $user = $this->createStaff(['password' => bcrypt('HELLOKITTY1234')]);

        $response = $this->post(route('login.login', ['username' => $user->email, 'password' => 'NOTHELLOKITTY1234']));

        $response->assertStatus(302);
        $response->assertRedirect(route('login.show'));
        $response->assertSessionHasErrors(['invalid']);
    }

    /** @test */
    public function an_invalid_username_fails()
    {
        config(['ldap.authentication' => true]);
        $user = $this->createStaff(['username' => 'fred', 'password' => bcrypt('HELLOKITTY1234')]);

        $response = $this->post(route('login.login', ['username' => "NOTfred", 'password' => 'HELLOKITTY1234']));

        $response->assertStatus(302);
        $response->assertRedirect(route('login.show'));
        $response->assertSessionHasErrors(['invalid']);
    }
}
