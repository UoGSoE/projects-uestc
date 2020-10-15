<?php

namespace Database\Factories;

use App\Models\ProjectLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectLink::class;

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
                return \App\Models\Project::factory()->create()->id;
            },
        ];
    }
}
