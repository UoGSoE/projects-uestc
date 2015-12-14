<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StudentChoicesTest extends TestCase
{
    use DatabaseTransactions;

    protected $staff, $student, $course, $type, $project1, $project2, $project3;

    public function testStudentSeesAppropriateProjects()
    {
        $this->buildWorld();

        $this->actingAs($this->student)
            ->visit('/')
            ->see($this->project1->title)
            ->dontSee($this->project2->title)
            ->dontSee($this->project3->title);
    }

    public function testStudentMustPickFiveDifferentChoices()
    {
        $this->buildWorld();

        $this->actingAs($this->student)
            ->visit('/')
            ->select($this->project1->id, 'choice[1]')
            ->press('submit')
            ->see('You must pick');
    }

    private function buildWorld()
    {
        $this->staff = factory(App\User::class)->create(['is_student' => false]);
        $this->student = factory(App\User::class)->create(['is_student' => true]);
        $this->course = factory(App\Course::class)->create();
        $this->type = factory(App\ProjectType::class)->create();
        $this->project1 = factory(App\Project::class)->create(['type_id' => $this->type->id, 'user_id' => $this->staff->id]);
        $this->project2 = factory(App\Project::class)->create(['type_id' => $this->type->id, 'user_id' => $this->staff->id]);
        $this->project3 = factory(App\Project::class)->create([
            'maximum_students' => 1, 'type_id' => $this->type->id, 'user_id' => $this->staff->id
        ]);
        $this->project1->courses()->sync([$this->course->id]);
        $this->project2->courses()->detach();
        $this->project3->courses()->sync([$this->course->id]);
        $this->project3->students()->sync([$this->student->id => ['accepted' => true]]);
        $this->course->students()->sync([$this->student->id]);
    }
}
