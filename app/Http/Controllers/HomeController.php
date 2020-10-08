<?php

namespace App\Http\Controllers;

use App\Project;
use App\ProjectConfig;
use Illuminate\Http\Request;

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
        if (! auth()->user()->degree_type) {
            return view('profile.edit_degree');
        }

        return view(
            'project.student_index',
            [
                'applicationsEnabled' => Project::applicationsEnabled(),
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
                            ),
                ],
                'unique_supervisors' => [
                    'UESTC' => config('projects.uestc_unique_supervisors'),
                    'UoG' => config('projects.uog_unique_supervisors'),
                ],
            ]
        );
    }

    public function staffHomepage()
    {
        return view('project.staff_index', ['projects' => auth()->user()->projects]);
    }
}
