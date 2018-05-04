<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\ProjectOversubscribedException;
use App\User;
use App\Project;
use App\ProjectConfig;

class BulkAllocateController extends Controller
{
    public function edit()
    {
        $students = User::students()->orderBy('surname')->get();
        $singleDegreeReq = ProjectConfig::getOption('single_uog_required_choices', config('projects.single_uog_required_choices'))
            + ProjectConfig::getOption('single_uestc_required_choices', config('projects.single_uestc_required_choices'));
        $dualDegreeReq = ProjectConfig::getOption('required_choices', config('projects.uog_required_choices'))
            + ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices'));
        $requiredChoices = $singleDegreeReq >= $dualDegreeReq ? $singleDegreeReq : $dualDegreeReq;
        return view('report.bulk_allocation', compact('students', 'requiredChoices'));
    }

    public function update(Request $request)
    {
        if (!$request->filled('student')) {
            return redirect()->back();
        }
        foreach ($request->student as $student_id => $project_id) {
            $student = User::findOrFail($student_id);
            $project = Project::findOrFail($project_id);
            if ($project->isFull()) {
                return redirect()->route('bulkallocate.edit')->withErrors([
                    'oversubscribed' => "Cannot allocate student {$student->fullName()} to {$project->title} - project has been filled"
                ]);
            }
            $project->acceptStudent($student);
        }
        return redirect()->route('bulkallocate.edit')->with('success_message', 'Allocations saved');
    }
}
