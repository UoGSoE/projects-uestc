<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\ProjectConfig;

class AdminBulkActiveTest extends DuskTestCase
{
    /** @test */
    public function admin_can_bulk_set_project_is_active_flags()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject(['is_active' => false]);
        $project2 = $this->createProject(['is_active' => true]);

        $this->browse(function ($browser) use ($project1, $project2, $admin) {
            $browser->loginAs($admin)
                    ->visit('/')
                    ->clickLink('Reports')
                    ->clickLink('Bulk Active/Inactive')
                    ->assertSee('Projects - Bulk Inactive/Active')
                    ->assertSee($project1->title)
                    ->assertSee($project2->title)
                    ->radio("statuses[{$project1->id}]", 1)
                    ->radio("statuses[{$project2->id}]", 0)
                    ->press('Update')
                    ->assertSee('Changes saved');
        });
    }
}
