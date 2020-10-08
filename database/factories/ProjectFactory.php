<?php

namespace Database\Factories;

use App\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

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
}
