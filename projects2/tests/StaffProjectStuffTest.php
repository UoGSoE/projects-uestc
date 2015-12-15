<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StaffProjectStuffTest extends TestCase
{
    use DatabaseTransactions;

    public function testStaffSeeTheirOwnProjectList()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/')
            ->see($this->project3->title);
    }

    public function testStaffCanCreateProject()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/')
            ->click('New Project')
            ->see('Create')
            ->submitForm([
                'title' => 'PSPSPSPSP',
                'description' => '12121212121212',
                'courses' => [$this->course->id],
                'type_id' => $this->type->id,
                'maximum_students' => 599
            ])
            ->see('Project Details')
            ->see('PSPSPSPSP')
            ->see('12121212121212')
            ->see($this->course->title)
            ->see(599);
    }

    public function testCreateFailsForMissingTitleOrCourses()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/')
            ->click('New Project')
            ->see('Create')
            ->submitForm([
                'title' => '',
                'description' => '12121212121212',
                'courses' => [$this->course->id],
                'type_id' => $this->type->id,
                'maximum_students' => 599
            ])
            ->see('is required')
            ->submitForm([
                'title' => 'PSPSPSPSP',
                'description' => '12121212121212',
                'courses' => [],
                'type_id' => $this->type->id,
                'maximum_students' => 599
            ])
            ->see('is required');
    }

    public function testEditProjectWorks()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/')
            ->see($this->project1->title)
            ->click($this->project1->title)
            ->see('Project Details')
            ->click('Edit')
            ->submitForm([
                'title' => 'XXXXXXXXXXXXXXXXXXXXXXXXX',
                'description' => '12121212121212',
                'courses' => [$this->course->id],
                'type_id' => $this->type->id,
                'maximum_students' => 599
            ])
            ->see('Project Details')
            ->see('XXXXXXXXXXXXXXXXXXXXXXXXX');
    }

    public function testCopyingAProject()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->visit('/')
            ->see($this->project2->title)
            ->click($this->project2->title)
            ->see('Project Details')
            ->click('Edit')
            ->click('Copy This Project')
            ->submitForm([
                'title' => 'LLLLLLLLLLLLLLLLLLL',
                'description' => '12121212121212',
                'courses' => [$this->course->id],
                'type_id' => $this->type->id,
                'maximum_students' => 599
            ])
            ->see('Project Details')
            ->see('LLLLLLLLLLLLLLLLLLL');
    }

    public function testAcceptingAStudentsFirstChoice()
    {
        // Note: when a student is accepted all their other choices are flushed from the system
        $this->buildWorld();
        $this->seeInDatabase('project_student', [
            'user_id' => $this->student->id,
            'project_id' => $this->project3->id,
            'choice' => 1,
            'accepted' => false
        ]);
        $this->seeInDatabase('project_student', [
            'user_id' => $this->student->id,
            'project_id' => $this->project5->id,
            'choice' => 2,
        ]);
        $this->actingAs($this->staff)
            ->visit('/')
            ->see($this->project3->title)
            ->click($this->project3->title)
            ->see('Project Details')
            ->see($this->student->fullName())
            ->check("accepted[{$this->student->id}]")
            ->press('Allocate')
            ->see('Allocations Saved');
        $this->seeInDatabase('project_student', [
            'user_id' => $this->student->id,
            'project_id' => $this->project3->id,
            'choice' => 1,
            'accepted' => true
        ]);
        $this->dontSeeInDatabase('project_student', [
            'user_id' => $this->student->id,
            'project_id' => $this->project5->id,
            'choice' => 2,
        ]);
    }

    public function testCantAcceptAStudentsSecondChoice()
    {
        $this->buildWorld();
        $this->seeInDatabase('project_student', [
            'user_id' => $this->student2->id,
            'project_id' => $this->project3->id,
            'choice' => 2,
            'accepted' => false
        ]);
        $this->actingAs($this->staff)
            ->visit('/')
            ->see($this->project3->title)
            ->click($this->project3->title)
            ->see('Project Details')
            ->see($this->student2->fullName())
            ->dontSee("accepted[{$this->student2->id}]")
            ->press('Allocate')
            ->dontSee('Allocations Saved')
            ->see('Project Details');
        $this->seeInDatabase('project_student', [
            'user_id' => $this->student2->id,
            'project_id' => $this->project3->id,
            'choice' => 2,
            'accepted' => false
        ]);
    }

    public function testCantViewOtherPeoplesProjectsOrDetails()
    {
        $this->buildWorld();
        $this->actingAs($this->staff)
            ->get("/project/{$this->staff2project->id}")
            ->assertResponseStatus(403);
        $this->actingAs($this->staff)
            ->get("/user/{$this->staff2->id}")
            ->assertResponseStatus(403);
    }

    private function buildWorld()
    {
        $this->staff = factory(App\User::class)->create(['is_student' => false]);
        $this->staff2 = factory(App\User::class)->create(['is_student' => false]);
        $this->student = factory(App\User::class)->create(['is_student' => true]);
        $this->student2 = factory(App\User::class)->create(['is_student' => true]);
        $this->course = factory(App\Course::class)->create();
        $this->type = factory(App\ProjectType::class)->create();
        $this->project1 = factory(App\Project::class)->create(['type_id' => $this->type->id, 'user_id' => $this->staff->id, 'is_active' => true]);
        $this->project2 = factory(App\Project::class)->create(['type_id' => $this->type->id, 'user_id' => $this->staff->id, 'is_active' => true]);
        $this->project3 = factory(App\Project::class)->create([
            'maximum_students' => 1, 'type_id' => $this->type->id, 'user_id' => $this->staff->id, 'is_active' => true
        ]);
        $this->project4 = factory(App\Project::class)->create(['type_id' => $this->type->id, 'user_id' => $this->staff->id, 'is_active' => true]);
        $this->project5 = factory(App\Project::class)->create(['type_id' => $this->type->id, 'user_id' => $this->staff->id, 'is_active' => true]);
        $this->staff2project = factory(App\Project::class)->create(['type_id' => $this->type->id, 'user_id' => $this->staff2->id, 'is_active' => true]);
        $this->project1->courses()->sync([$this->course->id]);
        $this->project2->courses()->detach();
        $this->project3->courses()->sync([$this->course->id]);
        $this->project3->students()->sync([
            $this->student->id => ['accepted' => false, 'choice' => 1],
            $this->student2->id => ['accepted' => false, 'choice' => 2]
        ]);
        $this->project4->courses()->sync([$this->course->id]);
        $this->project5->courses()->sync([$this->course->id]);
        $this->staff2project->courses()->sync([$this->course->id]);
        $this->project5->students()->sync([$this->student->id => ['accepted' => false, 'choice' => 2]]);
    }
}
