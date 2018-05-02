<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\ProjectConfig;

class HomeController extends Controller
{
    public function show()
    {
        if (auth()->user()->isStaff()) {
            return $this->staffHomepage();
        }
        return $this->studentHomepage();
    }

    public function studentHomepage()
    {
        if (!auth()->user()->degree_type) {
            return view('profile.edit_degree');
        }
        return view(
            'project.student_index', [
                'applicationsEnabled' => Project::applicationsEnabled(),
                'singleDegree' => auth()->user()->degree_type == 'Single',
                'required' => [
                    'uestc' => ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6)),
                    'uog' => ProjectConfig::getOption('required_choices', config('projects.uog_required_choices', 3))
                ],
            ]
        );
    }

    public function staffHomepage()
    {
        return view('project.staff_index', ['projects' => auth()->user()->projects]);
    }
}
