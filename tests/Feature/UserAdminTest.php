<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Notifications\StaffPasswordNotification;
use App\ProjectConfig;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use DatabaseMigrations;

    public function test_staff_admin_routes_cant_be_accessed_by_regular_users()
    {
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($regularUser)
                        ->get(route('staff.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_current_staff()
    {
        $adminUser = factory(User::class)->states('admin')->create();
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($adminUser)
                        ->get(route('staff.index'));

        $response->assertStatus(200);
        $response->assertSee('Current Staff');
        $response->assertSee($adminUser->username);
        $response->assertSee($regularUser->username);
    }

    public function test_admin_can_create_a_new_user()
    {
        $adminUser = factory(User::class)->states('admin')->create();

        $response = $this->actingAs($adminUser)
                        ->post(route('user.store'), [
                            'username' => 'HELLOKITTY',
                            'surname' => 'Kitty',
                            'forenames' => 'Hello',
                            'is_student' => false,
                            'is_convenor' => true,
                            'email' => 'hellokitty@example.com',
                            'institution' => 'UoG'
                        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'hellokitty@example.com', 'is_convenor' => true, 'institution' => 'UoG']);
    }

    public function test_admin_can_edit_an_existing_user()
    {
        $adminUser = factory(User::class)->states('admin')->create();
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($adminUser)
                        ->post(route('user.update', $regularUser->id), [
                            'username' => 'HELLOKITTY',
                            'surname' => 'Kitty',
                            'forenames' => 'Hello',
                            'is_student' => false,
                            'email' => 'hellokitty@example.com',
                            'institution' => 'UESTC',
                        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'hellokitty@example.com', 'institution' => 'UESTC']);
        $this->assertDatabaseMissing('users', ['email' => $regularUser->email]);
    }

    public function test_admin_can_delete_a_user()
    {
	$this->disableExceptionHandling();

        $adminUser = factory(User::class)->states('admin')->create();
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($adminUser)->get(route('user.destroy', $regularUser->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', ['email' => $regularUser->email]);
    }

    public function test_admin_can_preallocate_a_student_to_a_project()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $project = $this->createProject();

        $response = $this->actingAs($admin)
                        ->post(route('user.update', $student->id), [
                            'username' => 'HELLOKITTY',
                            'surname' => 'Kitty',
                            'forenames' => 'Hello',
                            'is_student' => true,
                            'email' => 'hellokitty@example.com',
                            'project_id' => $project->id
                        ]);

        $response->assertStatus(302);
        $this->assertTrue($project->fresh()->manually_allocated);
        $this->assertTrue($student->isAllocated());
        $this->assertEquals(1, $student->projects()->count());
    }

    /** @test */
    public function if_a_student_is_already_allocated_they_cant_be_manually_preallocated()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $project = $this->createProject();
        $project->acceptStudent($student);

        $response = $this->actingAs($admin)->get(route('user.edit', $student->id));

        $response->assertStatus(200);
        $response->assertDontSee($project->title);
        $response->assertDontSee('inputProject');
    }

    /** @test */
    public function if_a_project_is_full_it_doesnt_show_up_for_preallocation()
    {
        ProjectConfig::setOption('round', 1);
        $admin = $this->createAdmin();
        $student = $this->createStudent();
        $project = $this->createProject(['maximum_students' => 1]);
        $project->acceptStudent($student);
        $project2 = $this->createProject();
        $student2 = $this->createStudent();

        $response = $this->actingAs($admin)->get(route('user.edit', $student2->id));

        $response->assertStatus(200);
        $response->assertDontSee($project->title);
        $response->assertSee('inputProject');
        $response->assertSee($project2->title);
    }

    /** @test */
    public function can_import_a_list_of_staff_from_a_spreadsheet_with_new_users()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $filename = 'tests/data/test_staff.xlsx';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_staff.xlsx', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);

        $response = $this->actingAs($admin)
                        ->call('POST', route('staff.do_import'), [], [], ['file' => $file]);

        $response->assertStatus(200);
        $response->assertSee('New Users');
        $response->assertSee('Send Email');
        $this->assertDatabaseHas('users', ['email' => 'abcd@qpwoeiryt.io']);
        $this->assertDatabaseHas('users', ['email' => 'xyz@gmail.com']);
        $this->assertDatabaseHas('users', ['email' => $staff->email]);
    }

    /** @test */
    public function deleting_a_student_removes_all_their_project_choices_and_round_stats()
    {
        ProjectConfig::setOption('round', 1);
        $student = $this->createStudent();
        $student2 = $this->createStudent();
        $admin = $this->createAdmin();
        $project1 = $this->createProject();
        $project2 = $this->createProject();
        $project1->addStudent($student);
        $project2->addStudent($student);
        $project2->addStudent($student2);

        $student->delete();

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
        $this->assertDatabaseMissing('project_student', ['user_id' => $student->id]);
        $this->assertDatabaseMissing('project_rounds', ['user_id' => $student->id]);
        $this->assertDatabaseHas('project_student', ['user_id' => $student2->id]);
        $this->assertDatabaseHas('project_rounds', ['user_id' => $student2->id]);
    }
}
