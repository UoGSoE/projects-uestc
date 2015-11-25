<?php

use App\Role;
use App\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $allPermissions = Permission::all();
        $siteAdmin = Role::whereTitle('site_admin')->first();
        foreach ($allPermissions as $permission) {
            $siteAdmin->givePermissionTo($permission);
        }

        $basicAdmin = Permission::whereTitle('basic_admin')->first();
        $editUserRoles = Permission::whereTitle('edit_user_roles')->first();
        $editUsers = Permission::whereTitle('edit_users')->first();
        $seeReports = Permission::whereTitle('see_reports')->first();
        $allocateStudents = Permission::whereTitle('allocate_students')->first();
        $editCourses = Permission::whereTitle('edit_courses')->first();

        $teachingOffice = Role::whereTitle('teaching_office')->first();
        $teachingOffice->givePermissionTo($basicAdmin);
        $teachingOffice->givePermissionTo($editUserRoles);
        $teachingOffice->givePermissionTo($editUsers);
        $teachingOffice->givePermissionTo($seeReports);
        $teachingOffice->givePermissionTo($allocateStudents);
        $teachingOffice->givePermissionTo($editCourses);

        $convenor = Role::whereTitle('convenor')->first();
        $convenor->givePermissionTo($basicAdmin);
        $convenor->givePermissionTo($seeReports);
        $convenor->givePermissionTo($allocateStudents);

        Model::reguard();
    }
}
