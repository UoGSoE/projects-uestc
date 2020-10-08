<?php

namespace Database\Factories;

use App\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

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
}
