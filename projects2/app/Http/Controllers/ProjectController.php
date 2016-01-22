<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use App\User;
use App\Course;
use App\Project;
use App\EventLog;
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
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.create', compact('project', 'types', 'programmes', 'courses', 'staff'));
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
            'type_id' => 'required|integer',
            'maximum_students' => 'required|integer|min:1',
            'user_id' => 'required|integer|min:1'
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
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.edit', compact('project', 'types', 'programmes', 'courses', 'staff'));
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
            'type_id' => 'required|integer',
            'maximum_students' => 'required|integer|min:1',
            'user_id' => 'required|integer|min:1'
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
        EventLog::log(Auth::user()->id, "Made a copy of project {$project->title}");
        return view('project.create', compact('project', 'types', 'programmes', 'courses', 'staff'));
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
        $data = [];
        foreach ($request->accepted as $student_id => $accepted) {
            $data[$student_id] = [ 'accepted' => $accepted ];
            if ($accepted) {
                $student = User::findOrFail($student_id);
                $student->projects()->sync([$id]);
            }
        }
        $project->students()->sync($data);
        EventLog::log(Auth::user()->id, "Accepted students onto project {$project->title}");
        return redirect()->action('ProjectController@show', $project->id)->with('success_message', 'Allocations Saved');
    }

    public function bulkAllocate(Request $request)
    {
        if (!$request->has('student')) {
            return redirect()->back();
        }
        foreach ($request->student as $student_id => $project_id) {
            $student = User::findOrFail($student_id);
            $data[$project_id] = [
                'accepted' => true
            ];
            $student->projects()->sync($data);
        }
        return redirect()->action('ReportController@bulkAllocate')->with('success_message', 'Allocations saved');
    }

    public function bulkEditActive()
    {
        $projects = Project::orderBy('title')->get();
        return view('project.bulk_active', compact('projects'));
    }

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
