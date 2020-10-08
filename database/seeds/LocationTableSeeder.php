<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class LocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('locations')->insert([
            'title' => 'Glasgow',
            'is_default' => true,
        ]);
        Model::reguard();
    }
}
