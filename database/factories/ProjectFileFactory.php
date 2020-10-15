<?php

namespace Database\Factories;

use App\Models\ProjectFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectFile::class;

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
                return \App\Models\Project::factory()->create()->id;
            },
        ];
    }
}
