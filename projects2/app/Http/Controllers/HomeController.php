<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Project;
use App\ProjectConfig;

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
        //dd(Auth::user()->availableProjectsJson());
        return view(
            'project.student_index',
            ['applicationsEnabled' => Project::applicationsEnabled(),
             'requiredUoGChoices' => ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices', 3)),
             'requiredUESTCChoices' => ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6))]
        );
    }

    public function staffHomepage()
    {
        return view('project.staff_index', ['projects' => Auth::user()->projects]);
    }
}
