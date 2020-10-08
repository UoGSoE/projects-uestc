<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

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
            'label' => 'Basic Admin',
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_site_permissions',
            'label' => 'Create/Edit Website Permissions & Roles',
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_user_roles',
            'label' => 'Edit User Roles',
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_users',
            'label' => 'Create/Edit Users',
        ]);
        DB::table('permissions')->insert([
            'title' => 'see_reports',
            'label' => 'See Reports',
        ]);
        DB::table('permissions')->insert([
            'title' => 'allocate_students',
            'label' => 'Allocate Students To Projects',
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_courses',
            'label' => 'Create/Edit Courses & Programmes',
        ]);
        DB::table('permissions')->insert([
            'title' => 'edit_projects',
            'label' => 'Create/Edit Projects',
        ]);
        DB::table('permissions')->insert([
            'title' => 'login_as_user',
            'label' => 'Log in as another user',
        ]);
        DB::table('permissions')->insert([
            'title' => 'view_users',
            'label' => 'View users details',
        ]);
        DB::table('permissions')->insert([
            'title' => 'choose_any_location',
            'label' => 'Choose from any location',
        ]);
        DB::table('permissions')->insert([
            'title' => 'view_eventlog',
            'label' => 'View the activity log',
        ]);

        Model::reguard();
    }
}
