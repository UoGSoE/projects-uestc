<?php

use Illuminate\Database\Seeder;

use App\User;
use App\Project;
use App\Discipline;
use App\Course;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        $course = factory(Course::class)->create();
        $projects = factory(Project::class, 100)->create()->each(function ($project) use ($course) {
            $project->courses()->sync([$course->id]);
        });
        $students = factory(User::class, 30)->states('student')->create();
        $course->students()->sync($students->pluck('id'));
    }
}
