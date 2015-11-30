<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Course;
use App\Project;
use App\Location;
use App\Programme;
use App\ProjectType;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $types = ProjectType::orderBy('title')->get();
        $programmes = Programme::orderBy('title')->get();
        if (Auth::user()->location_id) {
            $courses = Course::where('location_id', '=', Auth::user()->location_id)->orderBy('title')->get();
        } else {
            $courses = Course::orderBy('title')->get();
        }
        $locations = Location::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.create', compact('project', 'types', 'programmes', 'courses', 'locations', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $project = new Project;
        $project->fill($request->input());
        if ($request->location_id > 0) {
            $project->location_id = $request->location_id;
        } else {
            $project->location_id = null;
        }
        $project->save();
        $project->courses()->sync($request->courses);
        $project->programmes()->sync($request->programmes);
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
        $types = ProjectType::orderBy('title')->get();
        $programmes = Programme::orderBy('title')->get();
        if (Auth::user()->location_id) {
            $courses = Course::where('location_id', '=', Auth::user()->location_id)->orderBy('title')->get();
        } else {
            $courses = Course::orderBy('title')->get();
        }
        $locations = Location::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.edit', compact('project', 'types', 'programmes', 'courses', 'locations', 'staff'));
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
        $project = Project::findOrFail($id);
        $project->fill($request->input());
        if ($request->location_id > 0) {
            $project->location_id = $request->location_id;
        } else {
            $project->location_id = null;
        }
        $project->save();
        $project->courses()->sync($request->courses);
        $project->programmes()->sync($request->programmes);
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
        Project::destroy($id);
        return redirect()->action('ProjectController@index');
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
        $locations = Location::orderBy('title')->get();
        $staff = User::staff()->orderBy('surname')->get();
        return view('project.create', compact('project', 'types', 'programmes', 'courses', 'locations', 'staff'));
    }

    public function acceptStudents(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        if (!$request->has('accepted')) {
            return redirect()->action('ProjectController@show', $project->id)->with('success_message', 'No changes');
        }
        $data = [];
        foreach ($request->accepted as $student_id => $accepted) {
            $data[$student_id] = [ 'accepted' => $accepted ];
        }
        $project->students()->sync($data);
        return redirect()->action('ProjectController@show', $project->id)->with('success_message', 'Allocations Saved');
    }
}