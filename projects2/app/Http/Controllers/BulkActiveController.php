<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BulkActiveController extends Controller
{
    /**
     * Show the form to let admins bulk-edit whether projects are active or not
     * @return view
     */
    public function edit()
    {
        $projects = Project::orderBy('title')->get();
        return view('project.bulk_active', compact('projects'));
    }

    /**
     * Bulk save whether projects are active or not
     * @param  Request $request
     * @return redirect
     */
    public function update(Request $request)
    {
        if (! $request->has('statuses')) {
            return redirect()->route('bulkactive.edit')->with('success_message', 'No changes made');
        }
        foreach ($request->statuses as $projectId => $status) {
            Project::findOrFail($projectId)->update(['is_active' => $status]);
        }
        return redirect()->route('bulkactive.edit')->with('success_message', 'Changes saved');
    }
}
