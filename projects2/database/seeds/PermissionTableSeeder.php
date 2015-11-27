<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        DB::table('permissions')->insert([
            'title' => 'basic_admin',
            'label' => 'Basic Admin'
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_site_permissions',
            'label' => 'Create/Edit Website Permissions & Roles'
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_user_roles',
            'label' => 'Edit User Roles'
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_users',
            'label' => 'Create/Edit Users'
        ]);
        DB::table('permissions')->insert([
            'title' => 'see_reports',
            'label' => 'See Reports'
        ]);
        DB::table('permissions')->insert([
            'title' => 'allocate_students',
            'label' => 'Allocate Students To Projects'
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_courses',
            'label' => 'Create/Edit Courses & Programmes'
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_projects',
            'label' => 'Create/Edit Projects'
        ]);

        Model::reguard();
    }
}
