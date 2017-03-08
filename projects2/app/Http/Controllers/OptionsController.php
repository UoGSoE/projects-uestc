<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectConfig;

class OptionsController extends Controller
{
    public function edit()
    {
    }

    public function update(Request $request)
    {
        if ($request->has('logins_allowed')) {
            ProjectConfig::setOption('logins_allowed', $request->logins_allowed);
        }
        if ($request->has('required_choices')) {
            ProjectConfig::setOption('required_choices', $request->required_choices);
        }
        if ($request->has('maximum_applications')) {
            ProjectConfig::setOption('maximum_applications', $request->maximum_applications);
        }
        if ($request->has('current_round')) {
            ProjectConfig::setOption('current_round', $request->current_round);
        }
        if ($request->has('applications_allowed')) {
            ProjectConfig::setOption('applications_allowed', $request->applications_allowed);
        }
        return redirect()->route('options.edit')->with('success_message', 'Options Updated');
    }
}
