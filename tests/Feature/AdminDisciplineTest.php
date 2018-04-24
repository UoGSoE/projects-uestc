<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminDisciplineTest extends TestCase
{
    use DatabaseMigrations;

    public function test_admin_can_view_discipline_list()
    {
        $admin = $this->createAdmin();
        $discipline = $this->createDiscipline();

        $response = $this->actingAs($admin)->get(route('discipline.index'));

        $response->assertStatus(200);
        $response->assertSee('Disciplines');
        $response->assertSee($discipline->title);
    }

    public function test_admin_can_create_a_new_discipline()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
                        ->post(route('discipline.store'), ['title' => 'ANEWTITLE']);

        $response->assertStatus(302);
        $this->assertDatabaseHas('disciplines', ['title' => 'ANEWTITLE']);
        $response->assertRedirect(route('discipline.index'));
    }

    public function test_admin_cant_create_a_discipline_with_a_duplicate_title()
    {
        $admin = $this->createAdmin();
        $discipline = $this->createDiscipline();

        $response = $this->actingAs($admin)->fromUrl(route('discipline.create'))
                        ->post(route('discipline.store'), ['title' => $discipline->title]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
        $response->assertRedirect(route('discipline.create'));
    }

    public function test_admin_can_edit_a_discipline()
    {
        $admin = $this->createAdmin();
        $discipline = $this->createDiscipline();

        $response = $this->actingAs($admin)->fromUrl(route('discipline.edit', $discipline->id))
                        ->post(route('discipline.update', $discipline->id), ['title' => 'WHATEVAH']);
        $response->assertStatus(302);
        $response->assertRedirect(route('discipline.index'));
        $this->assertDatabaseHas('disciplines', ['title' => 'WHATEVAH']);

        // double-check for unique title rule ignores the current discipline when editing
        $response = $this->actingAs($admin)
                        ->post(route('discipline.update', $discipline->id), ['title' => 'WHATEVAH']);
        $response->assertStatus(302);
        $response->assertRedirect(route('discipline.index'));
    }

    public function test_admin_cant_edit_a_discipline_and_set_its_title_to_a_duplicate()
    {
        $admin = $this->createAdmin();
        $discipline1 = $this->createDiscipline();
        $discipline2 = $this->createDiscipline();

        $response = $this->actingAs($admin)->fromUrl(route('discipline.edit', $discipline1->id))
                        ->post(route('discipline.update', $discipline1->id), ['title' => $discipline2->title]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
        $response->assertRedirect(route('discipline.edit', $discipline1->id));
    }

}
