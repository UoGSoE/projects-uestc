<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\Location;
use App\ProjectType;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function allProjects()
    {
        $projects = Project::with('owner', 'students', 'acceptedStudents', 'type')->orderBy('title')->get();
        $types = ProjectType::orderBy('title')->get();
        return view('report.all_projects', compact('projects', 'types'));
    }

    public function allProjectsOfType($typeId)
    {
        $projects = Project::where('type_id', '=', $typeId)
                    ->with('owner', 'students', 'acceptedStudents', 'type')
                    ->orderBy('title')
                    ->get();
        $types = ProjectType::orderBy('title')->get();
        return view('report.all_projects', compact('projects', 'types'));
    }

    public function allStudents()
    {
        $students = User::students()->with('courses', 'projects')->orderBy('surname')->get();
        return view('report.all_students', compact('students'));
    }

    public function allStaff()
    {
        $users = User::staff()->with('projects')->orderBy('surname')->get();
        return view('report.all_staff', compact('users'));
    }
}
