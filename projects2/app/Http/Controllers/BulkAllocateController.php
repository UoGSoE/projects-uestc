<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class BulkAllocateController extends Controller
{
    public function edit()
    {
        $students = User::students()->with('courses', 'projects')->orderBy('surname')->get();
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
            $student->projects()->sync($data);
        }
        return redirect()->route('bulkallocate.edit')->with('success_message', 'Allocations saved');
    }
}
