<?php
// @codingStandardsIgnoreFile

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\ProjectConfig;

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
                            'email' => 'hellokitty@example.com'
                        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'hellokitty@example.com', 'is_convenor' => true]);
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
                            'email' => 'hellokitty@example.com'
                        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', ['email' => 'hellokitty@example.com']);
        $this->assertDatabaseMissing('users', ['email' => $regularUser->email]);
    }

    public function test_admin_can_delete_a_user()
    {
        $adminUser = factory(User::class)->states('admin')->create();
        $regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($adminUser)->delete(route('user.destroy', $regularUser->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('users', ['email' => $regularUser->email]);
    }

    /** @test */
    public function can_import_a_list_of_staff_from_a_spreadsheet()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $filename = 'tests/data/test_staff.xlsx';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_staff.xlsx', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);

        $response = $this->actingAs($admin)
                        ->call('POST', route('staff.do_import'), [], [], ['file' => $file]);

        $response->assertStatus(302);
        $response->assertRedirect(route('staff.index'));
        $response->assertSessionHas('success_message');
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
