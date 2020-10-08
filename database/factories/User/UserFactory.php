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

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => implode(' ', $this->faker->words(3)),
            'description' => $this->faker->paragraph(3),
            'maximum_students' => $this->faker->numberBetween(3, 10),
            'is_active' => true,
            'discipline_id' => null,
            'institution' => 'UoG',
            'user_id' => function () {
                return \App\User::factory()->staff()->create()->id;
            },
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'filename' => $this->faker->word.'.'.$this->faker->fileExtension,
            'original_filename' => $this->faker->word.'.'.$this->faker->fileExtension,
            'file_size' => $this->faker->numberBetween(1000, 10000),
            'project_id' => function () {
                return \App\Project::factory()->create()->id;
            },
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'url' => $this->faker->url,
            'project_id' => function () {
                return \App\Project::factory()->create()->id;
            },
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => implode(' ', $this->faker->words(3)),
            'code' => 'ENG'.$this->faker->numberBetween(1000, 9999),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word.$this->faker->word.$this->faker->word,
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => $this->faker->randomNumber(),
            'token' => strtolower(Str::random(32)),
        ];
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->word,
        ];
    }
}
