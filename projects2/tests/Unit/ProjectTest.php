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

        $this->fail('Added a student to project which already has the maximum accepted without throwing an exception');
    }

    public function test_can_add_multiple_students()
    {
        $project = factory(Project::class)->create();
        $student1 = factory(User::class)->states('student')->create();
        $student2 = factory(User::class)->states('student')->create();

        $project->addStudent($student1);
        $project->addStudent($student2);

        $this->assertEquals(2, $project->students()->count());
    }

    public function test_cannot_add_the_same_student_more_than_once()
    {
        $project = factory(Project::class)->create();
        $student = factory(User::class)->states('student')->create();

        $project->addStudent($student);
        $project->addStudent($student);

        $this->assertEquals(1, $project->students()->count());
    }

    public function test_accepting_a_student_removes_other_students_from_the_project_if_only_one_allowed()
    {
        $project = factory(Project::class)->create(['maximum_students' => 1]);
        $student1 = factory(User::class)->states('student')->create();
        $student2 = factory(User::class)->states('student')->create();
        $project->addStudent($student1);
        $project->addStudent($student2);

        $project->acceptStudent($student1);

        $this->assertEquals(1, $project->students()->count());
        $this->assertDatabaseHas('project_student', ['user_id' => $student1->id, 'accepted' => true]);
        $this->assertDatabaseMissing('project_student', ['user_id' => $student2->id]);
    }

    public function test_accepting_a_student_removes_other_students_from_the_project_if_now_filled()
    {
        $project = factory(Project::class)->create(['maximum_students' => 2]);
        $student1 = factory(User::class)->states('student')->create();
        $student2 = factory(User::class)->states('student')->create();
        $student3 = factory(User::class)->states('student')->create();
        $project->addStudent($student1);
        $project->addStudent($student2);
        $project->addStudent($student3);

        $project->acceptStudent($student1);
        $project->acceptStudent($student2);

        $this->assertEquals(2, $project->students()->count());
        $this->assertDatabaseHas('project_student', ['user_id' => $student1->id, 'accepted' => true]);
        $this->assertDatabaseHas('project_student', ['user_id' => $student2->id, 'accepted' => true]);
        $this->assertDatabaseMissing('project_student', ['user_id' => $student3->id]);
    }
}
