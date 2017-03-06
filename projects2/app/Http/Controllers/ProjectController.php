<?php

namespace App\Http\Controllers;

use Auth;
use Gate;
use App\User;
use App\Course;
use App\Project;
use App\EventLog;
use App\Discipline;
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
        $courses = Course::orderBy('title')->get();
        $disciplines = Discipline::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.create', compact('project', 'courses', 'staff', 'disciplines'));
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
        if ($request->has('links')) {
            $project->syncLinks($request->links);
        }
        if ($request->hasFile('files')) {
            $project->addFiles($request->file('files'));
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
        $courses = Course::orderBy('title')->get();
        $disciplines = Discipline::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.edit', compact('project', 'courses', 'staff', 'disciplines'));
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
            'links.*.url' => 'url',
        ]);
        $project = Project::findOrFail($id);
        if (Gate::denies('edit_this_project', $project)) {
            abort(403);
        }
        $project->fill($request->input());
        $project->save();
        if ($request->has('student_id')) {
            $project->acceptStudent($request->student_id);
        }
        if ($request->has('links')) {
            $project->syncLinks($request->links);
        }
        if ($request->hasFile('files')) {
            $project->addFiles($request->file('files'));
        }
        if ($request->has('deletefiles')) {
            $project->deleteFiles($request->deletefiles);
        }
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
    public function copy($id)
    {
        $project = Project::findOrFail($id)->replicate();
        $courses = Course::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        $disciplines = Discipline::orderBy('title')->get();
        EventLog::log(Auth::user()->id, "Copied project {$project->title}");
        return view('project.create', compact('project', 'courses', 'staff', 'disciplines'));
    }

    public function acceptStudent(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        if (!$project->isAvailable()) {
            return redirect()->route('project.show', $id)->withErrors(['full' => 'This project cannot accept students']);
        }
        $student = User::findOrFail($request->accepted);
        if ($student->isAllocated()) {
            return redirect()->route('project.show', $id)->withErrors(['already_allocated' => 'That student has been accepted on a project already']);
        }
        $project->acceptStudent($student);
        EventLog::log(Auth::user()->id, "Accepted student {$student->fullName()} onto project {$project->title}");
        return redirect()->route('project.show', $project->id)->with('success_message', 'Allocations Saved');
    }
}
