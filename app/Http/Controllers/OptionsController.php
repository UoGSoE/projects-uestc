<?php

namespace App\Http\Controllers;

use App\User;
use Carbon\Carbon;
use App\ProjectConfig;
use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function edit()
    {
        $single_uog_required_choices = ProjectConfig::getOption('single_uog_required_choices', config('projects.single_uog_required_choices'));
        $single_uestc_required_choices = ProjectConfig::getOption('single_uestc_required_choices', config('projects.single_uestc_required_choices'));
        $dual_uog_required_choices = ProjectConfig::getOption('required_choices', config('projects.uog_required_choices'));
        $dual_uestc_required_choices = ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices'));
        $maximum_applications = ProjectConfig::getOption('maximum_applications', config('projects.maximumAllowedToApply'));
        $round = ProjectConfig::getOption('round', 1);
        $logins_allowed = ProjectConfig::getOption('logins_allowed', true);
        $applications_allowed = ProjectConfig::getOption('applications_allowed', 1);
        $project_edit_start = ProjectConfig::getOption('project_edit_start', Carbon::now()->format('d/m/Y'));
        $project_edit_end = ProjectConfig::getOption('project_edit_end', Carbon::now()->addMonths(1)->format('d/m/Y'));
        return view('options.edit', compact('dual_uog_required_choices', 'dual_uestc_required_choices', 'single_uog_required_choices', 'single_uestc_required_choices', 'maximum_applications', 'round', 'logins_allowed', 'applications_allowed', 'project_edit_start', 'project_edit_end'));
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'logins_allowed' => 'integer',
            'applications_allowed' => 'integer',
            'single_uog_required_choices' => 'required|integer',
            'single_uestc_required_choices' => 'required|integer',
            'dual_uog_required_choices' => 'required|integer',
            'dual_uestc_required_choices' => 'required|integer',
            'maximum_applications' => 'required|integer',
            'round' => 'required|integer|min:1|max:3',
            'project_edit_start' => 'required|date_format:d/m/Y',
            'project_edit_end' => 'required|date_format:d/m/Y',
        ]);
        ProjectConfig::setOption('logins_allowed', $request->logins_allowed);
        ProjectConfig::setOption('applications_allowed', $request->applications_allowed);
        ProjectConfig::setOption('single_uog_required_choices', $request->single_uog_required_choices);
        ProjectConfig::setOption('single_uestc_required_choices', $request->single_uestc_required_choices);
        ProjectConfig::setOption('required_choices', $request->dual_uog_required_choices);
        ProjectConfig::setOption('uestc_required_choices', $request->dual_uestc_required_choices);
        ProjectConfig::setOption('maximum_applications', $request->maximum_applications);
        ProjectConfig::setOption('round', $request->round);
        ProjectConfig::setOption('project_edit_start', $request->project_edit_start);
        ProjectConfig::setOption('project_edit_end', $request->project_edit_end);
        return redirect()->route('options.edit')->with('success_message', 'Options Updated');
    }

    //Deletes all project allocations
    public function destroy()
    {
        User::students()->each(function ($student, $key) {
            $student->projects()->sync([]);
        });
        return redirect()->route('options.edit')->with('success_message', 'All allocations deleted');
    }
}
