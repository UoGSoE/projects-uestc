<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PasswordResetTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    public function test_valid_reset_token_and_new_password()
    {
        $user = factory(App\User::class)->create(['is_student' => false]);
        $token = factory(App\PasswordReset::class)->create(['user_id' => $user->id]);
        $password = str_random(20);
        $this->visit("/resetpassword/{$token->token}")
            ->see('Reset your password')
            ->type($password, 'password1')
            ->type($password, 'password2')
            ->press('Reset and log in')
            ->see('Your Projects');
    }

    public function test_valid_reset_token_but_password_too_short()
    {
        $user = factory(App\User::class)->create(['is_student' => false]);
        $token = factory(App\PasswordReset::class)->create(['user_id' => $user->id]);
        $password = str_random(2);
        $this->visit("/resetpassword/{$token->token}")
            ->see('Reset your password')
            ->type($password, 'password1')
            ->type($password, 'password2')
            ->press('Reset and log in')
            ->see('Password was too short');
    }

    public function test_valid_reset_token_but_passwords_different()
    {
        $user = factory(App\User::class)->create(['is_student' => false]);
        $token = factory(App\PasswordReset::class)->create(['user_id' => $user->id]);
        $password = str_random(20);
        $this->visit("/resetpassword/{$token->token}")
            ->see('Reset your password')
            ->type($password, 'password1')
            ->type("hello", 'password2')
            ->press('Reset and log in')
            ->see('Passwords did not match');
    }

    public function test_expired_reset_token()
    {
        $user = factory(App\User::class)->create(['is_student' => false]);
        $token = factory(App\PasswordReset::class)->create(['user_id' => $user->id]);
        $token->created_at = \Carbon\Carbon::now()->subDays(100);
        $token->save();
        $this->visit("/resetpassword/{$token->token}")
            ->see('Token has expired');
    }

    public function test_invalid_reset_token()
    {
        $user = factory(App\User::class)->create(['is_student' => false]);
        $token = factory(App\PasswordReset::class)->create(['user_id' => $user->id]);
        $this->visit("/resetpassword/XYZ{$token->token}")
            ->see('Invalid token');
    }
}
