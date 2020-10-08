<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AdminOrConvenorReportsTest extends TestCase
{
    /** @test */
    public function admin_or_convenor_can_see_the_reports_menu()
    {
        $admin = $this->createAdmin();
        $convenor = $this->createConvenor();

        $adminResponse = $this->actingAs($admin)->get('/');
        $convenorResponse = $this->actingAs($convenor)->get('/');

        $adminResponse->assertSee('Reports');
        $convenorResponse->assertSee('Reports');
    }
}
