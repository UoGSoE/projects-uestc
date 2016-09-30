<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StudentLoginsDisabledTest extends TestCase
{
    use Illuminate\Foundation\Testing\DatabaseTransactions;

    public function testStudentLoginDoesntWorkWhenDisabled()
    {
        $student = factory(App\User::class)->create(
            ['username' => '1234567x', 'is_student' => true, 'password' => bcrypt('HELLO')]
        );
        config(['projects.studentsDisabled' => true]);
        $this->visit('/')
            ->see('log in using')
            ->type($student->username, 'username')
            ->type('HELLO', 'password')
            ->press('Sign in')
            ->see('Student logins are currently disabled');
    }
    
    public function testStudentLoginWorksWhenNotDisabled()
    {
        $student = factory(App\User::class)->create(
            ['username' => '1234567x', 'is_student' => true, 'password' => bcrypt('HELLO')]
        );
        config(['projects.studentsDisabled' => false]);
        $this->visit('/')
            ->see('log in using')
            ->type($student->username, 'username')
            ->type('HELLO', 'password')
            ->press('Sign in')
            ->dontSee('Student logins are currently disabled')
            ->see('Available Projects');
    }

    public function testStaffLoginsAlwaysWork()
    {
        $staff = factory(App\User::class)->create(
            ['username' => 'abc1x', 'is_student' => false, 'password' => bcrypt('HELLO')]
        );

        config(['projects.studentsDisabled' => true]);
        $this->visit('/')
            ->see('log in using')
            ->type($staff->username, 'username')
            ->type('HELLO', 'password')
            ->press('Sign in')
            ->dontSee('Student logins are currently disabled')
            ->see('Your Projects')
            ->click('Log Out');

        config(['projects.studentsDisabled' => false]);
        $this->visit('/')
            ->see('log in using')
            ->type($staff->username, 'username')
            ->type('HELLO', 'password')
            ->press('Sign in')
            ->dontSee('Student logins are currently disabled')
            ->see('Your Projects');
    }
}
