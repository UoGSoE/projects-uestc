<?php
// @codingStandardsIgnoreFile

namespace Tests\Unit;

use App\Project;
use App\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ClearUnsuccessfulStudentTest extends TestCase
{
    use DatabaseMigrations;

    public function test_can_remove_all_unsuccessful_student_choices()
    {
        $project1 = factory(Project::class)->create();
        $project2 = factory(Project::class)->create(['maximum_students' => 2]);
        $student1 = factory(User::class)->states('student')->create();
        $student2 = factory(User::class)->states('student')->create();
        $project1->addStudent($student1);
        $project1->addStudent($student2);
        $project2->acceptStudent($student1);
        $project2->addStudent($student2);

        Project::clearAllUnsucessfulStudents();

        $this->assertEquals(0, $project1->students()->count());
        $this->assertEquals(1, $project2->students()->count());

    }
}
