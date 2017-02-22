<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Project;

class HomeController extends Controller
{
    public function show()
    {
        if (Auth::user()->isStaff()) {
            return $this->staffHomepage();
        }
        return $this->studentHomepage();
    }

    public function studentHomepage()
    {
        return view(
            'project.student_index',
            ['applicationsEnabled' => Project::applicationsEnabled(),
             'requiredProjectChoices' => config('projects.requiredProjectChoices')]
        );
    }

    public function staffHomepage()
    {
        return view('project.staff_index', ['projects' => Auth::user()->projects]);
    }
}
