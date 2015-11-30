<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function allProjects()
    {
        $projects = Project::with('owner', 'students', 'acceptedStudents', 'location', 'type')->orderBy('title')->get();
        return view('report.all_projects', compact('projects'));
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
