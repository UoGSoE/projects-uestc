<?php

use App\Course;
use App\Discipline;
use App\Project;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        Mail::fake();
        $admin = factory(User::class)->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'email' => 'admin@example.com',
            'surname' => 'admin',
            'forenames' => 'admin',
            'is_admin' => true,
        ]);
        $course = factory(Course::class)->create();
        $course2 = factory(Course::class)->create();
        $disciplines = [
            'Communications',
            'Control',
            'Electronics',
            'Embedded Processors / Systems',
            'Image processing',
            'Machine learning',
            'Power',
            'Signal Processing',
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
        $students = factory(User::class, 15)->states('student')->create(['degree_type' => 'Single']);
        $course->students()->sync($students->pluck('id'));
    }
}
