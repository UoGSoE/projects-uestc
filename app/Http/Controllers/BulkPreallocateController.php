<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class BulkPreallocateController extends Controller
{
    public function edit()
    {
        $projects = Project::active()->get()->filter(function ($project) {
            return $project->canAcceptAStudent();
        });
        $students = User::students()->get()->filter(function ($student) {
            return $student->unallocated();
        });

        return view('report.bulk_preallocate', compact('projects', 'students'));
    }

    public function update(Request $request)
    {
        if (! $request->filled('project')) {
            return redirect()->back();
        }
        foreach ($request->project as $projectId => $studentId) {
            if (! $studentId) {
                continue;
            }
            $project = Project::findOrFail($projectId);
            if ($project->canAcceptAStudent()) {
                $project->preAllocate($studentId);
            }
        }

        return redirect()->route('bulkpreallocate.edit')->with('success_message', 'Allocated');
    }
}
