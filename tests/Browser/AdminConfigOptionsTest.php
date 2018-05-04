<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class AdminConfigOptionsTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function admin_can_set_config_options()
    {
        $admin = $this->createAdmin();

        $this->browse(function ($browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit(route('report.projects'))
                    ->clickLink('Options')
                    ->assertSee('Site Options')
                    ->type('maximum_applications', '4')
                    ->type('single_uog_required_choices', '0')
                    ->type('single_uestc_required_choices', '9')
                    ->type('dual_uog_required_choices', '3')
                    ->type('dual_uestc_required_choices', '6')
                    ->type('round', '2')
                    ->type('project_edit_start', Carbon::now()->subDays(7)->format('d/m/Y'))
                    ->type('project_edit_end', Carbon::now()->addDays(7)->format('d/m/Y'))
                    ->check('logins_allowed')
                    ->check('applications_allowed')
                    ->press('Update')
                    ->assertPathIs('/admin/options')
                    ->assertSee('Options Updated')
                    ->assertInputValue('maximum_applications', '4')
                    ->assertInputValue('single_uog_required_choices', '0')
                    ->assertInputValue('single_uestc_required_choices', '9')
                    ->assertInputValue('dual_uog_required_choices', '3')
                    ->assertInputValue('dual_uestc_required_choices', '6')
                    ->assertInputValue('round', '2')
                    ->assertInputValue('project_edit_start', Carbon::now()->subDays(7)->format('d/m/Y'))
                    ->assertInputValue('project_edit_end', Carbon::now()->addDays(7)->format('d/m/Y'))
                    ->assertChecked('logins_allowed')
                    ->assertChecked('applications_allowed');
        });
    }
}
