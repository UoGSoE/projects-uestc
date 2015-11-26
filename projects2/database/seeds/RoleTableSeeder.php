<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('roles')->insert([
            'title' => 'site_admin',
            'label' => 'Site Administrator'
        ]);
        DB::table('roles')->insert([
            'title' => 'teaching_office',
            'label' => 'Teaching Office Staff'
        ]);
        DB::table('roles')->insert([
            'title' => 'convenor',
            'label' => 'Project Convenor'
        ]);

        Model::reguard();
    }
}
