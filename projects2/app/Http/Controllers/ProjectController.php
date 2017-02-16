<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use App\User;
use App\Course;
use App\Project;
use App\EventLog;
use App\Discipline;
use App\Programme;
use App\ProjectType;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = Project::orderBy('title')->get();
        return view('project.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $project = new Project;
        $project->is_active = false;
        $project->maximum_students = 1;
        $project->user_id = Auth::user()->id;
        $types = ProjectType::orderBy('title')->get();
        $programmes = Programme::orderBy('title')->get();
        $courses = Course::orderBy('title')->get();
        $disciplines = Discipline::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.create', compact('project', 'types', 'programmes', 'courses', 'staff', 'disciplines'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required',
            'courses' => 'required|array',
            'maximum_students' => 'required|integer|min:1',
            'user_id' => 'required|integer',
        ]);

        $project = new Project;
        $project->fill($request->input());
        $project->save();
        $project->courses()->sync($request->courses);
        if ($request->has('programmes')) {
            $project->programmes()->sync($request->programmes);
        }
        EventLog::log(Auth::user()->id, "Created project {$project->title}");
        return redirect()->action('ProjectController@show', $project->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $project = Project::findOrFail($id);
        if (Gate::denies('view_this_project', $project)) {
            abort(403);
        }
        return view('project.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $project = Project::findOrFail($id);
        if (Gate::denies('edit_this_project', $project)) {
            abort(403);
        }
        $types = ProjectType::orderBy('title')->get();
        $programmes = Programme::orderBy('title')->get();
        $courses = Course::orderBy('title')->get();
        $disciplines = Discipline::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.edit', compact('project', 'types', 'programmes', 'courses', 'staff', 'disciplines'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required',
            'courses' => 'required|array',
            'maximum_students' => 'required|integer|min:1',
            'user_id' => 'required|integer',
        ]);
        $project = Project::findOrFail($id);
        if (Gate::denies('edit_this_project', $project)) {
            abort(403);
        }
        $project->fill($request->input());
        $project->save();
        $project->courses()->sync($request->courses);
        //$project->programmes()->sync($request->programmes);
        EventLog::log(Auth::user()->id, "Updated project {$project->title}");
        return redirect()->action('ProjectController@show', $project->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        if (Gate::denies('edit_this_project', $project)) {
            abort(403);
        }
        EventLog::log(Auth::user()->id, "Deleted project {$project->title}");
        $project->delete();
        return redirect()->to('/')->with('success_message', 'Project deleted');
    }

    /**
     * Make a copy of an existing project.
     * Note: The replicate() function copies the main object but not it's relations, so we don't copy
     * students allocations etc to the new version.
     * @param  integer $id Project ID
     * @return Response
     */
    public function duplicate($id)
    {
        $project = Project::findOrFail($id)->replicate();
        $types = ProjectType::orderBy('title')->get();
        $programmes = Programme::orderBy('title')->get();
        $courses = Course::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        $disciplines = Discipline::orderBy('title')->get();
        EventLog::log(Auth::user()->id, "Copied project {$project->title}");
        return view('project.create', compact('project', 'types', 'programmes', 'courses', 'staff', 'disciplines'));
    }

    /**
     * Accept (or un-accept) students onto projects
     * @param  Request $request
     * @param  integer  $id      The project ID
     * @return Response
     */
    public function acceptStudents(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        if (!$request->has('accepted')) {
            return redirect()->action('ProjectController@show', $project->id)->with('success_message', 'No changes');
        }
        $studentList = $this->buildListOfStudents($request, $id);
        $this->setThisAsOnlyChoiceForAcceptedStudents($studentList, $id);
        $project->students()->sync($studentList);
        EventLog::log(Auth::user()->id, "Accepted students onto project {$project->title}");
        return redirect()->action('ProjectController@show', $project->id)->with('success_message', 'Allocations Saved');
    }

    /**
     * Builds an array suitable for ->sync() on projects based on the 'accepted[]' request input
     * @param  Request $request   The form $request object
     * @param  integer $projectId The project->id
     * @return array            Array of data as $data[student_id] => ['accepted' => boolean]
     */
    private function buildListOfStudents($request, $projectId)
    {
        $data = [];
        foreach ($request->accepted as $studentId => $accepted) {
            $data[$studentId] = $this->acceptedPivotFlag($accepted);
        }
        return $data;
    }

    /**
     * Readable helper to build the sync() suitable pivot data
     * @param  boolean $accepted Whether or not a student was accepted
     * @return array
     */
    private function acceptedPivotFlag($accepted)
    {
        return [ 'accepted' => $accepted ];
    }

    /**
     * Loops over all the students passed and removes any other projects if they've been accepted onto this one
     * @param array $studentList Array of students from buildListOfStudents()
     * @param integer $projectId   ID of the project we're working with
     */
    private function setThisAsOnlyChoiceForAcceptedStudents($studentList, $projectId)
    {
        foreach ($studentList as $studentId => $accepted) {
            $this->updateStudentProjectsWhereAccepted($studentId, $projectId, $accepted['accepted']);
        }
    }

    /**
     * If the student was accepted onto the project - remove all other choices they've made via sync()
     * @param  integer $studentId
     * @param  integer $projectId
     * @param  boolean $accepted
     */
    private function updateStudentProjectsWhereAccepted($studentId, $projectId, $accepted)
    {
        if ($accepted) {
            $student = User::findOrFail($studentId);
            $student->projects()->sync([$projectId]);
        }
    }

    /**
     * Bulk allocate students to projects
     * @param  Request $request
     * @return redirect
     */
    public function bulkAllocate(Request $request)
    {
        if (!$request->has('student')) {
            return redirect()->back();
        }
        foreach ($request->student as $student_id => $project_id) {
            $student = User::findOrFail($student_id);
            $data[$project_id] = [ 'accepted' => true ];
            $student->projects()->sync($data);
        }
        return redirect()->action('ReportController@bulkAllocate')->with('success_message', 'Allocations saved');
    }

    /**
     * Show the form to let admins bulk-edit whether projects are active or not
     * @return view
     */
    public function bulkEditActive()
    {
        $projects = Project::orderBy('title')->get();
        return view('project.bulk_active', compact('projects'));
    }

    /**
     * Bulk save whether projects are active or not
     * @param  Request $request
     * @return redirect
     */
    public function bulkSaveActive(Request $request)
    {
        if (! $request->has('statuses')) {
            return redirect()->action('ProjectController@bulkEditActive')->with('success_message', 'No changes made');
        }
        foreach ($request->statuses as $projectId => $status) {
            Project::findOrFail($projectId)->update(['is_active' => $status]);
        }
        return redirect()->action('ProjectController@bulkEditActive')->with('success_message', 'Changes saved');
    }
}
