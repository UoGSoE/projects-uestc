<?php
// @codingStandardsIgnoreFile
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\User;
use App\Course;
use App\Project;

class StaffProjectTest extends TestCase
{
    use DatabaseMigrations;

    public $regularUser;

    public function test_staff_can_create_a_new_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.store'), $this->defaultProjectData());

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'DEFAULTTITLE', 'discipline_id' => 1]);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
    }

    public function test_staff_can_edit_their_own_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'UPDATEDPROJECT']);
        $this->assertDatabaseMissing('projects', ['title' => $project->title]);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
    }

    public function test_staff_cant_edit_someone_elses_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $user2 = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['title' => $project->title]);
    }

    public function test_admin_can_edit_someone_elses_project()
    {
        $admin = factory(User::class)->states('admin')->create();
        $user2 = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $user2->id]);

        $response = $this->actingAs($admin)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'UPDATEDPROJECT']);
    }

    public function test_staff_can_delete_their_own_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->delete(route('project.destroy', $project->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('projects', ['title' => $project->title]);
        $response->assertRedirect('/');
    }

    public function test_staff_cant_delete_someone_elses_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $user2 = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($user2)
                        ->delete(route('project.destroy', $project->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['title' => $project->title]);
    }

    public function test_staff_can_make_a_copy_of_their_project()
    {
        $this->regularUser = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->get(route('project.copy', $project->id));

        $response->assertStatus(200);
        $response->assertSee('Create A New Project');
        $response->assertSee($project->title);
    }

    public function test_staff_can_preallocate_a_student_to_a_project()
    {
        $staff = factory(User::class)->states('staff')->create();
        $student = factory(User::class)->states('student')->create();
        $project = factory(Project::class)->create(['user_id' => $staff->id]);

        $response = $this->actingAs($staff)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['student_id' => $student->id]));

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertDatabaseHas('project_student', ['project_id' => $project->id, 'user_id' => $student->id, 'accepted' => true]);
    }

    public function test_staff_can_add_links_to_a_project()
    {
        $staff = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $staff->id]);

        $response = $this->actingAs($staff)
                        ->post(route('project.update', $project->id), $this->defaultProjectData([
                            'links' => [
                                ['url' => 'http://www.example.com'], 
                                ['url' => 'http://www.another.com']
                            ]
                        ]));

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertDatabaseHas('project_links', ['project_id' => $project->id, 'url' => 'http://www.example.com']);
        $this->assertDatabaseHas('project_links', ['project_id' => $project->id, 'url' => 'http://www.another.com']);
    }

    public function test_staff_can_remove_links_from_a_project()
    {
        $staff = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $staff->id]);
        $project->links()->create(['url' => 'http://site1.com']);
        $project->links()->create(['url' => 'http://site2.com']);

        $response = $this->actingAs($staff)
                        ->post(route('project.update', $project->id), $this->defaultProjectData([
                            'links' => [
                                ['url' => 'http://site1.com'], 
                            ]
                        ]));

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertDatabaseHas('project_links', ['project_id' => $project->id, 'url' => 'http://site1.com']);
        $this->assertDatabaseMissing('project_links', ['project_id' => $project->id, 'url' => 'http://site2.com']);
    }

    public function test_staff_can_attach_files_to_a_project()
    {
        $staff = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $staff->id]);

        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $files = [
            'files' => [$file]
        ];
        $response = $this->actingAs($staff)
                        ->call('POST', route('project.update', $project->id), $this->defaultProjectData(), [], $files);

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertEquals(1, $project->files()->count());
    }

    public function test_staff_can_remove_existing_files_from_a_project()
    {
        $staff = factory(User::class)->states('staff')->create();
        $project = factory(Project::class)->create(['user_id' => $staff->id]);

        $filename = 'tests/data/test_cv.pdf';
        $file = new \Illuminate\Http\UploadedFile($filename, 'test_cv.pdf', 'application/pdf', filesize($filename), UPLOAD_ERR_OK, true);
        $files = [
            'files' => [$file]
        ];
        $project->addFiles($files['files']);
        $file = $project->files()->first();
    
        $response = $this->actingAs($staff)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['deletefiles' => [$file->id]]));

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertEquals(0, $project->files()->count());
    }


    protected function defaultProjectData($overrides = [])
    {
        $course = factory(Course::class)->create();
        return array_merge([
            'title' => 'DEFAULTTITLE',
            'description' => 'DEFAULTDESCRIPTION',
            'is_active' => true,
            'user_id' => $this->regularUser ? $this->regularUser->id : 1,
            'maximum_students' => 1,
            'courses' => [1 => $course->id],
            'discipline_id' => 1,
        ], $overrides);
    }
}
