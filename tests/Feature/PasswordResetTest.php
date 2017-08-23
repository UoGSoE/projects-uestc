<?php
// @codingStandardsIgnoreFile
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_valid_reset_token_and_new_password()
    {
        $user = $this->createStaff();
        $token = $this->createToken(['user_id' => $user->id]);
        $password = str_random(20);

        $response = $this->post(route('password.do_reset', $token), [
            'password1' => $password,
            'password2' => $password
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
    }

    public function test_valid_reset_token_but_password_too_short()
    {
        $user = $this->createStaff();
        $token = $this->createToken(['user_id' => $user->id]);
        $password = str_random(2);

        $response = $this->from(route('password.reset', $token->token))->post(route('password.do_reset', $token->token), [
            'password1' => $password,
            'password2' => $password
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('password.reset', $token->token));
        $response->assertSessionHasErrors(['password_length']);
    }

    public function test_valid_reset_token_but_passwords_different()
    {
        $user = $this->createStaff();
        $token = $this->createToken(['user_id' => $user->id]);
        $password = str_random(2);

        $response = $this->from(route('password.reset', $token->token))->post(route('password.do_reset', $token->token), [
            'password1' => $password,
            'password2' => "NOT" . $password
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('password.reset', $token->token));
        $response->assertSessionHasErrors(['password_mismatch']);
    }

    public function test_expired_reset_token()
    {
        $user = $this->createStaff();
        $token = $this->createToken(['user_id' => $user->id, 'created_at' => \Carbon\Carbon::now()->subDays(100)]);
        $password = str_random(20);

        $response = $this->from(route('password.reset', $token->token))->post(route('password.do_reset', $token->token), [
            'password1' => $password,
            'password2' => $password
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('password.reset', $token->token));
        $response->assertSessionHasErrors(['token_expired']);
    }

    public function test_invalid_reset_token()
    {
        $user = $this->createStaff();
        $password = str_random(20);

        $response = $this->from(route('password.reset', 'NOTATOKEN'))->post(route('password.do_reset', 'NOTATOKEN'), [
            'password1' => $password,
            'password2' => $password
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('password.reset', 'NOTATOKEN'));
        $response->assertSessionHasErrors(['token_invalid']);
    }
}
