<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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
                    ->type('required_choices', '5')
                    ->type('uestc_required_choices', '7')
                    ->type('round', '2')
                    ->check('logins_allowed')
                    ->check('applications_allowed')
                    ->press('Update')
                    ->assertPathIs('/admin/options')
                    ->assertSee('Options Updated')
                    ->assertInputValue('maximum_applications', '4')
                    ->assertInputValue('required_choices', '5')
                    ->assertInputValue('uestc_required_choices', '7')
                    ->assertInputValue('round', '2')
                    ->assertChecked('logins_allowed')
                    ->assertChecked('applications_allowed');
        });
    }
}
