<?php

namespace App\Http\Controllers;

use App\Course;
use App\Discipline;
use App\EventLog;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Project;
use App\ProjectConfig;
use App\User;
use Auth;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;

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
        $project->is_active = true;
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
        if (! $this->projectEditingAllowed()) {
            return redirect()->route('home')->withErrors(['dates' => 'Project editing currently disabled.']);
        }

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
        $project->disciplines()->sync($request->disciplines);
        if ($request->filled('links')) {
            $project->syncLinks($request->links);
        }
        if ($request->hasFile('files')) {
            $project->addFiles($request->file('files'));
        }
        EventLog::log(Auth::user()->id, "Created project {$project->title}");

        return redirect()->route('project.show', $project->id);
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
        if (! $this->projectEditingAllowed()) {
            return redirect()->route('home')->withErrors(['dates' => 'Project editing currently disabled.']);
        }

        $this->validate($request, [
            'title' => 'required|max:255',
            'description' => 'required',
            'courses' => 'required|array',
            'maximum_students' => 'required|integer|min:1',
            'user_id' => 'required|integer',
            'links.*.url' => 'nullable|url',
        ]);
        $project = Project::findOrFail($id);
        if (Gate::denies('edit_this_project', $project)) {
            abort(403);
        }
        $project->fill($request->input());
        $project->discipline_id = null;
        $project->save();
        if ($request->filled('student_id')) {
            $project->preAllocate($request->student_id);
        }
        if ($request->filled('links')) {
            $project->syncLinks($request->links);
        }
        if ($request->hasFile('files')) {
            $project->addFiles($request->file('files'));
        }
        if ($request->filled('deletefiles')) {
            $project->deleteFiles($request->deletefiles);
        }
        $project->courses()->sync($request->courses);
        $project->disciplines()->sync($request->disciplines);
        EventLog::log(Auth::user()->id, "Updated project {$project->title}");

        return redirect()->route('project.show', $project->id);
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
     * @param  int $id Project ID
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
        if (! $project->canAcceptAStudent()) {
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

    public function getProjectsJSON()
    {
        return response(Project::with('owner')->get()->toJson());
    }

    public function projectEditingAllowed()
    {
        if (Auth::user()->hasRoles()) {
            return true;
        }
        $start = Carbon::createFromFormat('d/m/Y', ProjectConfig::getOption('project_edit_start', Carbon::now()->subDays(1)->format('d/m/Y')));
        $end = Carbon::createFromFormat('d/m/Y', ProjectConfig::getOption('project_edit_end', Carbon::now()->addDays(1)->format('d/m/Y')));
        if (! Carbon::now()->between($start, $end)) {
            return false;
        }

        return true;
    }
}
