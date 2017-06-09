<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\ProjectConfig;

class ExportAllocationTest extends TestCase
{
    public function test_can_export_the_project_allocations_as_a_spreadsheet()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $student3 = $this->createStudent();
        $project1->acceptStudent($student1);
        $project2->preAllocate($student2);

        $response = $this->actingAs($admin)->get(route('export.allocations'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="allocations.xlsx"');
        // should really try loading the sheet into memory and testing, but it's
        // Friday afternoon - yolo.
    }

    public function test_can_export_the_student_list_as_a_spreadsheet()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $student3 = $this->createStudent();
        $project1->acceptStudent($student1);
        $project2->preAllocate($student2);

        $response = $this->actingAs($admin)->get(route('export.students'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="students.xlsx"');
        // should really try loading the sheet into memory and testing, but it's
        // Friday afternoon - yolo.
    }

    public function test_can_export_the_staff_list_as_a_spreadsheet()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $staff1 = $this->createStaff();
        $staff2 = $this->createStaff();
        $staff3 = $this->createStaff();
        $project1 = $this->createProject(['user_id' => $staff1->id]);
        $project2 = $this->createProject(['user_id' => $staff2->id]);
        $student1 = $this->createStudent();
        $student2 = $this->createStudent();
        $project1->acceptStudent($student1);
        $project2->preAllocate($student2);

        $response = $this->actingAs($admin)->get(route('export.staff'));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename="staff.xlsx"');
        // should really try loading the sheet into memory and testing, but it's
        // Friday afternoon - yolo.
    }
}
