<?php

// @codingStandardsIgnoreFile

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Discipline;
use App\Notifications\AllocatedToProject;
use App\Models\Project;
use App\Models\ProjectConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StaffProjectTest extends TestCase
{
    use DatabaseMigrations;

    public $regularUser;

    public function test_staff_can_create_a_new_project()
    {
        $this->regularUser = User::factory()->staff()->create();

        $response = $this->actingAs($this->regularUser)->get(route('project.create'));

        $response->assertStatus(200);

        $data = $this->defaultProjectData(['institution' => 'UESTC']);

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.store'), $data);

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'DEFAULTTITLE',  'institution' => 'UESTC']);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
        // 'title' => 'DEFAULTTITLE',
        // 'description' => 'DEFAULTDESCRIPTION',
        // 'is_active' => true,
        // 'user_id' => $this->regularUser ? $this->regularUser->id : 1,
        // 'maximum_students' => 1,
        // 'courses' => [1 => $course->id],
        // 'discipline_id' => 1,
        // 'institution' => 'UoG',

        $this->assertEquals($data['title'], $project->title);
        $this->assertEquals($data['description'], $project->description);
        $this->assertTrue($data['is_active'], $project->is_active);
        $this->assertEquals($data['user_id'], $project->user_id);
        $this->assertEquals($data['maximum_students'], $project->maximum_students);
        $this->assertEquals([1], $project->courses->pluck('id')->toArray());
        $this->assertEquals($data['institution'], $project->institution);
        $this->assertEquals($data['supervisor_name'], $project->supervisor_name);
        $this->assertEquals($data['supervisor_email'], $project->supervisor_email);
    }

    /** @test */
    public function staff_can_add_multiple_disciplines_to_a_project()
    {
        $staff = User::factory()->staff()->create();
        $courses = Course::factory()->count(2)->create();
        $disciplines = Discipline::factory()->count(3)->create();

        $response = $this->actingAs($staff)->post(route('project.store'), [
            'title' => 'Project Title',
            'description' => 'Project description',
            'prereq' => 'Project prerequisite skills',
            'is_active' => 1,
            'courses' => [
                $courses[0]->id,
            ],
            'disciplines' => [
                $disciplines[0]->id,
                $disciplines[1]->id,
            ],
            'institution' => 'UESTC',
            'maximum_students' => 1,
            'user_id' => $staff->id,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', [
            'title' => 'Project Title',
            'description' => 'Project description',
            'prereq' => 'Project prerequisite skills',
            'is_active' => 1,
            'institution' => 'UESTC',
            'user_id' => $staff->id,
        ]);
        $this->assertDatabaseHas('course_project', [
            'project_id' => Project::first()->id,
            'course_id' => $courses[0]->id,
        ]);
        $this->assertDatabaseHas('project_disciplines', [
            'project_id' => Project::first()->id,
            'discipline_id' => $disciplines[0]->id,
        ]);
        $this->assertDatabaseHas('project_disciplines', [
            'project_id' => Project::first()->id,
            'discipline_id' => $disciplines[1]->id,
        ]);
    }

    /** @test */
    public function staff_can_only_create_projects_between_valid_dates()
    {
        $this->regularUser = User::factory()->staff()->create();
        ProjectConfig::setOption('project_edit_start', Carbon::now()->addDays(7)->format('d/m/Y'));
        ProjectConfig::setOption('project_edit_end', Carbon::now()->addDays(14)->format('d/m/Y'));

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.store'), $this->defaultProjectData(['institution' => 'UESTC']));

        $response->assertStatus(302);
        $response->assertRedirect(route('home'));
        $response->assertSessionHasErrors('dates');
        $this->assertEquals(0, Project::count());
    }

    /** @test */
    public function admin_can_edit_projects_no_matter_what_the_date()
    {
        $this->admin = User::factory()->admin()->create();
        ProjectConfig::setOption('project_edit_start', Carbon::now()->addDays(7)->format('d/m/Y'));
        ProjectConfig::setOption('project_edit_end', Carbon::now()->addDays(14)->format('d/m/Y'));

        $response = $this->actingAs($this->admin)
                        ->post(route('project.store'), $this->defaultProjectData(['institution' => 'UESTC']));

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'DEFAULTTITLE', 'institution' => 'UESTC']);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
    }

    /** @test */
    public function check_dropdown_matches_users_institution_on_project_edit_page()
    {
        $this->regularUser = User::factory()->staff()->create(['institution' => 'UESTC']);

        $response = $this->actingAs($this->regularUser)
                        ->get(route('project.create'));

        $response->assertStatus(200);
        $response->assertSee('class="UESTC ', false); // the false param disables escaping the double-quote
    }

    public function test_staff_can_edit_their_own_project()
    {
        $this->regularUser = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.update', $project->id), $this->defaultProjectData([
                            'title' => 'UPDATEDPROJECT',
                            'supervisor_name' => 'Boris',
                            'supervisor_email' => 'boris@example.com',
                        ]));

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'UPDATEDPROJECT']);
        $this->assertDatabaseMissing('projects', ['title' => $project->title]);
        $project = Project::first();
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertEquals('Boris', $project->supervisor_name);
        $this->assertEquals('boris@example.com', $project->supervisor_email);
    }

    public function test_staff_cant_edit_someone_elses_project()
    {
        $this->regularUser = User::factory()->staff()->create();
        $user2 = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($this->regularUser)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['title' => $project->title]);
    }

    public function test_admin_can_edit_someone_elses_project()
    {
        $admin = User::factory()->admin()->create();
        $user2 = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($admin)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['title' => 'UPDATEDPROJECT']));

        $response->assertStatus(302);
        $this->assertDatabaseHas('projects', ['title' => 'UPDATEDPROJECT']);
    }

    public function test_staff_can_delete_their_own_project()
    {
        $this->regularUser = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->get(route('project.destroy', $project->id));

        $response->assertStatus(302);
        $this->assertDatabaseMissing('projects', ['title' => $project->title]);
        $response->assertRedirect('/');
    }

    public function test_staff_cant_delete_someone_elses_project()
    {
        $this->regularUser = User::factory()->staff()->create();
        $user2 = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($user2)
                        ->get(route('project.destroy', $project->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('projects', ['title' => $project->title]);
    }

    public function test_staff_can_make_a_copy_of_their_project()
    {
        $this->regularUser = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $this->regularUser->id]);

        $response = $this->actingAs($this->regularUser)
                        ->get(route('project.copy', $project->id));

        $response->assertStatus(200);
        $response->assertSee('Create A New Project');
        $response->assertSee($project->title);
    }

    //Removed as staff are no longer allowed to accept students on their projects
    // /** @test */
    // public function staff_can_accept_a_student_onto_a_project()
    // {
    //     ProjectConfig::setOption('round', 1);
    //     $staff = User::factory()->staff()->create();
    //     $student = User::factory()->student()->create();
    //     $project = Project::factory()->create(['user_id' => $staff->id]);

    //     $response = $this->actingAs($staff)
    //                     ->post(route('project.enrol', $project->id), ['accepted' => $student->id]);

    //     $response->assertStatus(302);
    //     $response->assertRedirect(route('project.show', $project->id));
    //     $this->assertDatabaseHas('project_student', ['project_id' => $project->id, 'user_id' => $student->id, 'accepted' => true]);
    // }

    // /** @test */
    // public function a_notification_is_sent_to_the_student_when_accepted_onto_a_project()
    // {
    //     Notification::fake();
    //     $staff = User::factory()->staff()->create();
    //     $student = User::factory()->student()->create();
    //     $project = Project::factory()->create(['user_id' => $staff->id]);

    //     $response = $this->actingAs($staff)
    //                     ->post(route('project.enrol', $project->id), ['accepted' => $student->id]);

    //     Notification::assertSentTo(
    //                 $student,
    //                 AllocatedToProject::class,
    //                 function ($notification, $channels) use ($project) {
    //                     return $notification->project->id === $project->id;
    //                 }
    //     );
    // }

    /** @test */
    /* This is to check for a race condition.  If two members of staff have projects which the
       same student has applied for - and both decide to accept them, then make sure the student
       doesn't get accepted twice.  For instance, first staff member opens the page then takes
       a phone call - in the meantime the other member of staff has accepted the student - when
       the call ends and they accept the same student it could get quite confusing for the
       student (and possibly DB)
    */
    // public function staff_cant_accept_a_student_onto_a_project_if_they_are_already_accepted_onto_one()
    // {
    //     ProjectConfig::setOption('round', 1);
    //     $staff1 = User::factory()->staff()->create();
    //     $staff2 = User::factory()->staff()->create();
    //     $student = User::factory()->student()->create();
    //     $project1 = Project::factory()->create(['user_id' => $staff1->id]);
    //     $project2 = Project::factory()->create(['user_id' => $staff2->id]);
    //     $project2->acceptStudent($student);

    //     $response = $this->actingAs($staff1)->fromUrl(route('project.show', $project1->id))
    //                     ->post(route('project.enrol', $project1->id), ['accepted' => $student->id]);

    //     $response->assertStatus(302);
    //     $response->assertRedirect(route('project.show', $project1->id));
    //     $response->assertSessionHasErrors(['already_allocated']);
    //     $this->assertDatabaseMissing('project_student', ['project_id' => $project1->id, 'user_id' => $student->id, 'accepted' => true]);
    // }

    public function test_staff_can_preallocate_a_student_to_a_project()
    {
        ProjectConfig::setOption('round', 1);
        $staff = User::factory()->staff()->create();
        $student = User::factory()->student()->create();
        $project = Project::factory()->create(['user_id' => $staff->id]);

        $response = $this->actingAs($staff)
                        ->post(route('project.update', $project->id), $this->defaultProjectData(['student_id' => $student->id]));

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertDatabaseHas('project_student', ['project_id' => $project->id, 'user_id' => $student->id, 'accepted' => true]);
    }

    public function test_staff_can_add_links_to_a_project()
    {
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $staff->id]);

        $response = $this->actingAs($staff)
                        ->post(route('project.update', $project->id), $this->defaultProjectData([
                            'links' => [
                                ['url' => 'http://www.example.com'],
                                ['url' => 'http://www.another.com'],
                            ],
                        ]));

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertDatabaseHas('project_links', ['project_id' => $project->id, 'url' => 'http://www.example.com']);
        $this->assertDatabaseHas('project_links', ['project_id' => $project->id, 'url' => 'http://www.another.com']);
    }

    public function test_staff_can_remove_links_from_a_project()
    {
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $staff->id]);
        $project->links()->create(['url' => 'http://site1.com']);
        $project->links()->create(['url' => 'http://site2.com']);

        $response = $this->actingAs($staff)
                        ->post(route('project.update', $project->id), $this->defaultProjectData([
                            'links' => [
                                ['url' => 'http://site1.com'],
                            ],
                        ]));

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertDatabaseHas('project_links', ['project_id' => $project->id, 'url' => 'http://site1.com']);
        $this->assertDatabaseMissing('project_links', ['project_id' => $project->id, 'url' => 'http://site2.com']);
    }

    public function test_staff_can_attach_files_to_a_project()
    {
        Storage::fake();
        $this->withoutExceptionHandling();
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $staff->id]);

        $filename = 'tests/data/test_cv.pdf';
        $file = UploadedFile::fake()->create('test_cv.pdf');
        $files = [
            'files' => [$file],
        ];
        $response = $this->actingAs($staff)
                        ->call('POST', route('project.update', $project->id), $this->defaultProjectData(), [], $files);

        $response->assertStatus(302);
        $response->assertRedirect(route('project.show', $project->id));
        $this->assertEquals(1, $project->files()->count());

        // remove test artifact
        $file = $project->files()->first();
        $file->removeFromDisk();
    }

    public function test_staff_can_remove_existing_files_from_a_project()
    {
        $staff = User::factory()->staff()->create();
        $project = Project::factory()->create(['user_id' => $staff->id]);

        $filename = 'tests/data/test_cv.pdf';
        $file = UploadedFile::fake()->create('test_cv.pdf');
        $files = [
            'files' => [$file],
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
        $course = Course::factory()->create();

        return array_merge([
            'title' => 'DEFAULTTITLE',
            'description' => 'DEFAULTDESCRIPTION',
            'is_active' => true,
            'user_id' => $this->regularUser ? $this->regularUser->id : 1,
            'maximum_students' => 1,
            'courses' => [1 => $course->id],
            'discipline_id' => 1,
            'institution' => 'UoG',
            'supervisor_name' => 'Big Bird',
            'supervisor_email' => 'squawk@example.com',
        ], $overrides);
    }
}
