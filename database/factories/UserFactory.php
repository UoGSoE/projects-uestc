<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => preg_replace('/\'/', '-', $this->faker->userName),
            'surname' => preg_replace('/\'/', '-', $this->faker->lastName),
            'forenames' => preg_replace('/\'/', '-', $this->faker->firstName),
            'email' => $this->faker->safeEmail(),
            'is_student' => false,
            'last_login' => $this->faker->dateTimeThisYear(),
            'is_admin' => false,
            'remember_token' => Str::random(10),
            'institution' => 'UoG',
            'degree_type' => 'Dual',
        ];
    }

    public function admin()
    {
        return $this->state(function () {
            return [
                'is_admin' => true,
                'is_student' => false,
            ];
        });
    }

    public function convenor()
    {
        return $this->state(function () {
            return [
                'is_convenor' => true,
                'is_student' => false,
            ];
        });
    }

    public function student()
    {
        return $this->state(function () {
            return [
                'is_student' => true,
                'username' => $this->faker->numberBetween(1000000, 9999999).$this->faker->randomLetter,
                'bio' => $this->faker->paragraph,
            ];
        });
    }

    public function staff()
    {
        return $this->state(function () {
            return [
                'is_student' => false,
            ];
        });
    }
}
