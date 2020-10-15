<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Discipline;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        Mail::fake();
        $admin = User::factory()->create([
            'username' => 'admin',
            'password' => bcrypt('secret'),
            'email' => 'admin@example.com',
            'surname' => 'admin',
            'forenames' => 'admin',
            'is_admin' => true,
        ]);
        $course = Course::factory()->create();
        $course2 = Course::factory()->create();
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
            $discipline = Discipline::factory()->create(['title' => $disciplineTitle]);
            $uogProjects = Project::factory()->count(3)->create(['institution' => 'UoG'])->each(function ($project) use ($course, $discipline) {
                $project->courses()->sync([$course->id]);
                $project->disciplines()->sync([$discipline->id]);
            });
            $uestcProjects = Project::factory()->count(3)->create(['institution' => 'UESTC'])->each(function ($project) use ($course, $discipline) {
                $project->courses()->sync([$course->id]);
                $project->disciplines()->sync([$discipline->id]);
            });
        }
        $students = User::factory()->count(15)->student()->create(['degree_type' => null]);
        $course->students()->sync($students->pluck('id'));
        $students = User::factory()->count(15)->student()->create(['degree_type' => 'Single']);
        $course->students()->sync($students->pluck('id'));
    }
}
