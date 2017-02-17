<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\EventLog;
use App\Project;

class StudentChoicesController extends Controller
{
    public function update(Request $request)
    {
        $student = $request->user();
        $picked = $request->choices;
        $requiredChoices = config('projects.requiredProjectChoices', 5);
        if (count($picked) != $requiredChoices) {
        dd($picked);
            return redirect()->back()->withErrors(['choice_number' => "You must pick {$requiredChoices} choices"]);
        }
        if (!$this->choicesAreAllDifferent($picked)) {
            return redirect()->to('/')->withErrors(['choice_diff' => 'You must pick {$requiredChoices} *different* projects']);
        }
        $student->allocateToProjects($picked);
        $projects = Project::whereIn('id', array_keys($picked))->pluck('title')->toArray();
        EventLog::log($student->id, "Chose projects " . implode(', ', $projects));
        return redirect()->to('/')->with(
            'success_message',
            'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.'
        );
    }

    private function choicesAreAllDifferent($choices)
    {
        return count($choices) == count(array_unique($choices));
    }


}
