<?php
// @codingStandardsIgnoreFile
namespace Tests\Feature;

use App\Notifications\AllocatedToProject;
use App\ProjectConfig;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ImportAllocationsTest extends TestCase
{
    /** @test */
    public function admin_can_view_import_student_allocations_page () {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('allocations.import'));

        $response->assertStatus(200);
        $response->assertSee('Import Student Allocations');
        $response->assertSee('Submit');
    }

    /** @test */
    public function non_authorised_users_cannot_view_import_student_allocations_page () {
        $student = $this->createStudent();

        $response = $this->actingAs($student)->get(route('allocations.import'));

        $response->assertStatus(200);
        $response->assertDontSee('Import Student Allocations');
        $response->assertDontSee('Submit');
    }

    /** @test */
    public function import_spreadsheet_to_allocate_student_projects () {
        Notification::fake();
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $project1 = $this->createProject(['title' => 'Project 1']);
        $project2 = $this->createProject(['title' => 'Project 2']);
        $project3 = $this->createProject(['title' => 'Project 3']);
        $project4 = $this->createProject(['title' => 'Project 4']);

        $student1 = $this->createStudent(['username' => '1111111']);
        $student2 = $this->createStudent(['username' => '2222222']);
        $student3 = $this->createStudent(['username' => '3333333']);
        $student4 = $this->createStudent(['username' => '4444444']);
        $student5 = $this->createStudent(['username' => '5555555']);

        $student6 = $this->createStudent();
        $assignedProject = $this->createProject(['title' => 'Assigned Project', 'maximum_students' => 1]);
        $assignedProject->acceptStudent($student6);


        copy('tests/data/allocations.xlsx', 'tests/data/allocations2.xlsx');

        $response = $this->actingAs($admin)->call('POST', route('allocations.do_import'), [], [], [
            'allocations' => new UploadedFile(base_path('tests/data/allocations2.xlsx'), 'allocations2.xlsx', null, null, null, true)]
        );

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['Invalid student', 'Invalid project', 'Project taken']);
        $this->assertEquals($student1->fresh()->allocatedProject()->id, $project1->id);
        $this->assertEquals($student2->fresh()->allocatedProject()->id, $project2->id);
        $this->assertEquals($student3->fresh()->allocatedProject()->id, $project3->id);
        Notification::assertSentTo([$student1, $student2, $student3], AllocatedToProject::class);
        Notification::assertNotSentTo([$student4, $student5], AllocatedToProject::class);
    }
}
