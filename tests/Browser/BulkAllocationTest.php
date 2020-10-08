<?php

// @codingStandardsIgnoreFile

namespace Tests\Browser;

use App\ProjectConfig;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class BulkAllocationTest extends DuskTestCase
{
    /** @test */
    public function admin_can_bulk_allocate_students_to_projects()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $course = $this->createCourse();
        list($project1, $project2) = factory(\App\Project::class, 2)->create()->each(function ($project) use ($course) {
            $project->courses()->sync([$course->id]);
        });
        $project1->addStudent($student1);
        $project2->addStudent($student2);
        $student1->courses()->sync([$course->id]);
        $student2->courses()->sync([$course->id]);

        $this->browse(function ($browser) use ($student1, $student2, $project1, $project2, $admin) {
            $browser->loginAs($admin)
                    ->visit('/')
                    ->clickLink('Reports')
                    ->clickLink('Bulk Allocations')
                    ->assertSee('This page lets you bulk-allocate students')
                    ->assertSee($project1->title)
                    ->assertSee($project2->title)
                    ->assertSee($student1->fullName())
                    ->assertSee($student2->fullName())
                    ->radio("student[{$student1->id}]", $project1->id)
                    ->radio("student[{$student2->id}]", $project2->id)
                    ->press('Allocate Choices')
                    ->assertSee('Allocations saved');
        });
    }
}
