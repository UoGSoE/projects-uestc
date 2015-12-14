<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StudentChoicesTest extends TestCase
{
    use DatabaseTransactions;

    public function testStudentSeesAppropriateProjects()
    {
        $staff = factory(App\User::class)->create(['is_student' => false]);
        $student = factory(App\User::class)->create(['is_student' => true]);
        $course = factory(App\Course::class)->create();
        $type = factory(App\ProjectType::class)->create();
        $project1 = factory(App\Project::class)->create(['type_id' => $type->id, 'user_id' => $staff->id]);
        $project2 = factory(App\Project::class)->create(['type_id' => $type->id, 'user_id' => $staff->id]);
        $project3 = factory(App\Project::class)->create([
            'maximum_students' => 1, 'type_id' => $type->id, 'user_id' => $staff->id
        ]);
        $project1->courses()->sync([$course->id]);
        $project2->courses()->detach();
        $project3->courses()->sync([$course->id]);
        $project3->students()->sync([$student->id => ['accepted' => true]]);
        $course->students()->sync([$student->id]);

        $this->actingAs($student)
            ->visit('/')
            ->see($project1->title)
            ->dontSee($project2->title)
            ->dontSee($project3->title);
    }
}
