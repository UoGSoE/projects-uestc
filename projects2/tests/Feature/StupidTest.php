<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class StupidTest extends TestCase
{
    use DatabaseMigrations;

    public function testMigrations()
    {
        $user = factory(\App\User::class)->create();
        dd(\App\User::count());
    }
}
