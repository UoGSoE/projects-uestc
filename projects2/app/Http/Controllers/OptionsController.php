<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectConfig;

class OptionsController extends Controller
{
    public function edit()
    {
        $required_choices = ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices'));
        $maximum_applications = ProjectConfig::getOption('maximum_applications', config('projects.maximumAllowedToApply'));
        $round = ProjectConfig::getOption('round', 1);
        $logins_allowed = ProjectConfig::getOption('logins_allowed', true);
        $applications_allowed = ProjectConfig::getOption('applications_allowed', 1);
        return view('options.edit', compact('required_choices', 'maximum_applications', 'round', 'logins_allowed', 'applications_allowed'));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'logins_allowed' => 'integer',
            'applications_allowed' => 'integer',
            'required_choices' => 'required|integer',
            'maximum_applications' => 'required|integer',
            'round' => 'required|integer|min:1|max:3',
        ]);
        ProjectConfig::setOption('logins_allowed', $request->logins_allowed);
        ProjectConfig::setOption('applications_allowed', $request->applications_allowed);
        ProjectConfig::setOption('required_choices', $request->required_choices);
        ProjectConfig::setOption('maximum_applications', $request->maximum_applications);
        ProjectConfig::setOption('round', $request->round);
        return redirect()->route('options.edit')->with('success_message', 'Options Updated');
    }
}
