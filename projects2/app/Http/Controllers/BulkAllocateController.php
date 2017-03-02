<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exceptions\ProjectOversubscribedException;
use App\User;
use App\Project;

class BulkAllocateController extends Controller
{
    public function edit()
    {
        $students = User::students()->orderBy('surname')->get();
        return view('report.bulk_allocation', compact('students'));
    }

    public function update(Request $request)
    {
        if (!$request->has('student')) {
            return redirect()->back();
        }
        foreach ($request->student as $student_id => $project_id) {
            $student = User::findOrFail($student_id);
            $data[$project_id] = [ 'accepted' => true ];
            $project = Project::findOrFail($project_id);
            if ($project->isFull()) {
                return redirect()->route('bulkallocate.edit')->withErrors([
                    'oversubscribed' => "Cannot allocate student {$student->fullName()} to {$project->title} - project has been filled"
                ]);
            }
            $student->projects()->sync($data);
        }
        return redirect()->route('bulkallocate.edit')->with('success_message', 'Allocations saved');
    }
}
