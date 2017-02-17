<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Project;
use App\Exceptions\ProjectOversubscribedException;

class ProjectTest extends TestCase
{
    use DatabaseMigrations;

    public function test_a_project_cannot_be_oversubscribed()
    {
        $project = factory(Project::class)->create();
        $student = factory(User::class)->states('student')->create();
        $otherStudents = factory(User::class, config('projects.maximumAllowedToApply'))->states('student')->create();
        $project->students()->saveMany($otherStudents);

        try {
            $project->addStudent($student);
        } catch (ProjectOversubscribedException $e) {
            return;
        }

        $this->fail('Added a student to an already fully subcribed project without throwing an exception');
    }

    public function test_a_project_cannot_accept_more_students_than_allowed()
    {
        $project = factory(Project::class)->create(['maximum_students' => 1]);
        $student1 = factory(User::class)->states('student')->create();
        $student2 = factory(User::class)->states('student')->create();
        $project->addStudent($student1);
        $project->acceptStudent($student1);

        try {
            $project->addStudent($student2);
        } catch (ProjectOversubscribedException $e) {
            return;
        }

        $this->fail('Added a student to an already fully subcribed project without throwing an exception');
    }

    public function test_cannot_add_the_same_student_more_than_once()
    {
        $project = factory(Project::class)->create();
        $student = factory(User::class)->states('student')->create();

        $project->addStudent($student);
        $project->addStudent($student);

        $this->assertEquals(1, $project->students()->count());
    }

}
