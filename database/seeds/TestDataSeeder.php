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
        $disciplines = [
            'Communications',
            'Control',
            'Electronics',
            'Embedded Processors / Systems',
            'Image processing',
            'Machine learning',
            'Power',
            'Signal Processing'
        ];
        foreach ($disciplines as $disciplineTitle) {
            $discipline = factory(Discipline::class)->create(['title' => $disciplineTitle]);
            $uogProjects = factory(Project::class, 3)->create(['institution' => 'UoG'])->each(function ($project) use ($course, $discipline) {
                $project->courses()->sync([$course->id]);
                $project->disciplines()->sync([$discipline->id]);
            });
            $uestcProjects = factory(Project::class, 3)->create(['institution' => 'UESTC'])->each(function ($project) use ($course, $discipline) {
                $project->courses()->sync([$course->id]);
                $project->disciplines()->sync([$discipline->id]);
            });
        }
        $students = factory(User::class, 15)->states('student')->create(['degree_type' => null]);
        $course->students()->sync($students->pluck('id'));
    }
}
