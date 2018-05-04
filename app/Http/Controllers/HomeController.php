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
                'singleDegree' => auth()->user()->isSingleDegree(),
                'required' => [
                    'uestc' => auth()->user()->isSingleDegree()
                            ? ProjectConfig::getOption(
                                'single_uestc_required_choices',
                                config('projects.single_uestc_required_choices')
                            )
                            : ProjectConfig::getOption(
                                'uestc_required_choices',
                                config('projects.uestc_required_choices')
                            ),
                    'uog' => auth()->user()->isSingleDegree()
                            ? ProjectConfig::getOption(
                                'single_uog_required_choices',
                                config('projects.single_uog_required_choices')
                            )
                            : ProjectConfig::getOption(
                                'required_choices',
                                config('projects.uog_required_choices')
                            )
                ],
            ]
        );
    }

    public function staffHomepage()
    {
        return view('project.staff_index', ['projects' => auth()->user()->projects]);
    }
}
