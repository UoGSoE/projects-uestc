<?php
// @codingStandardsIgnoreFile

namespace Tests\Browser;

use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminDisciplineTest extends DuskTestCase
{
    /** @test */
    public function admin_can_create_and_edit_a_discipline()
    {
        $adminUser = $this->createAdmin();
        $this->browse(function ($browser) use ($adminUser) {
            $browser->loginAs($adminUser)
                    ->visit(route('discipline.index'))
                    ->assertSee('Disciplines')
                    ->assertDontSee('HELLOKITTY')
                    ->clickLink('Add New Discipline')
                    ->type('title', 'HELLOKITTY')
                    ->press('Create')
                    ->assertSee('Disciplines')
                    ->assertSee('HELLOKITTY')
                    ->clickLink('HELLOKITTY')
                    ->assertSee('Edit Discipline')
                    ->type('title', 'MIFFYISEVIL')
                    ->press('Update')
                    ->assertSee('Disciplines')
                    ->assertDontSee('HELLOKITTY')
                    ->assertSee('MIFFYISEVIL');
        });
    }
}
