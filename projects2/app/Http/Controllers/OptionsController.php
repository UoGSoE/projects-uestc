<?php

namespace App\Http\Controllers;

use App\ProjectConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function edit()
    {
        $required_choices = ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices'));
        $uestc_required_choices = ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices'));
        $maximum_applications = ProjectConfig::getOption('maximum_applications', config('projects.maximumAllowedToApply'));
        $round = ProjectConfig::getOption('round', 1);
        $logins_allowed = ProjectConfig::getOption('logins_allowed', true);
        $applications_allowed = ProjectConfig::getOption('applications_allowed', 1);
        $project_edit_start = ProjectConfig::getOption('project_edit_start', Carbon::now()->format('d/m/Y'));
        $project_edit_end = ProjectConfig::getOption('project_edit_end', Carbon::now()->addMonths(1)->format('d/m/Y'));
        return view('options.edit', compact('required_choices', 'uestc_required_choices', 'maximum_applications', 'round', 'logins_allowed', 'applications_allowed', 'project_edit_start', 'project_edit_end'));
    }

    public function update(Request $request)
    {
        //dd('hi');
        $this->validate($request, [
            'logins_allowed' => 'integer',
            'applications_allowed' => 'integer',
            'required_choices' => 'required|integer',
            'uestc_required_choices' => 'required|integer',
            'maximum_applications' => 'required|integer',
            'round' => 'required|integer|min:1|max:3',
            'project_edit_start' => 'required|date_format:d/m/Y',
            'project_edit_end' => 'required|date_format:d/m/Y',
        ]);
        ProjectConfig::setOption('logins_allowed', $request->logins_allowed);
        ProjectConfig::setOption('applications_allowed', $request->applications_allowed);
        ProjectConfig::setOption('required_choices', $request->required_choices);
        ProjectConfig::setOption('uestc_required_choices', $request->uestc_required_choices);
        ProjectConfig::setOption('maximum_applications', $request->maximum_applications);
        ProjectConfig::setOption('round', $request->round);
        ProjectConfig::setOption('project_edit_start', $request->project_edit_start);
        ProjectConfig::setOption('project_edit_end', $request->project_edit_end);
        return redirect()->route('options.edit')->with('success_message', 'Options Updated');
    }
}
