<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\ProjectConfig;

class BulkPreAllocationTest extends DuskTestCase
{
    /** @test */
    public function admin_can_bulk_preallocate_students_to_projects()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        list($project1, $project2) = factory(\App\Project::class, 2)->create(['maximum_students' => 1]);
        $project3 = $this->createProject();

        $this->browse(function ($browser) use ($student1, $student2, $project1, $project2, $project3, $admin) {
            $browser->loginAs($admin)
                    ->visit('/')
                    ->clickLink('Bulk Preallocations')
                    ->assertSee('This page lets you bulk-preallocate students to projects')
                    ->assertSee($project1->title)
                    ->assertSee($project2->title)
                    ->assertSee($project3->title)
                    ->assertSee($student1->fullName())
                    ->assertSee($student2->fullName())
                    ->select("project[{$project1->id}]", "$student1->id")
                    ->select("project[{$project2->id}]", "$student2->id")
                    ->press('Allocate Choices')
                    ->assertSee('Allocated')
                    ->assertDontSee($project1->title)
                    ->assertDontSee($project2->title)
                    ->assertSee($project3->title);
        });
    }
}
