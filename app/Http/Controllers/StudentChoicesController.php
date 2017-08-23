<?php

namespace App\Http\Controllers;

use App\EventLog;
use App\Project;
use App\ProjectConfig;
use App\User;
use Illuminate\Http\Request;

class StudentChoicesController extends Controller
{
    public function index()
    {
    }

    public function update(Request $request)
    {
        if (!Project::applicationsEnabled()) {
            return redirect()->to('/')->withErrors(['disabled' => 'Applications are currently disabled']);
        }
        $student = $request->user();
        $picked = $request->choices;
        $correctAmountOfChoices = $this->validNumberOfChoices($picked);
        if ($correctAmountOfChoices !== true) {
            return $correctAmountOfChoices;
        }
        $result = $this->checkChoicesAreOk($picked);
        if ($result !== true) {
            return $result;
        }
        $student->allocateToProjects($picked);
        $projects = Project::whereIn('id', array_values($picked))->pluck('title')->toArray();
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

    public function choicesHaveDifferentSupervisors($choices)
    {
        $supervisorIds = Project::find($choices)->pluck('user_id')->all();
        $uniqueIds = array_unique($supervisorIds);
        if ($supervisorIds == $uniqueIds) {
            return true;
        }
        return false;
    }

    private function checkChoicesAreOk($choices)
    {
        if (!$this->choicesAreAllDifferent($choices)) {
            return redirect()->back()->withErrors([
                'choice_diff' => "You must pick {$requiredChoices} *different* projects"
            ]);
        }
        if (!$this->choicesHaveDifferentSupervisors($choices)) {
            return redirect()->back()->withErrors([
                'supervisor_diff' => "You cannot choose two projects that have the same supervisor"
            ]);
        }
        $projects = Project::whereIn('id', array_values($choices))->get();
        foreach ($projects as $project) {
            if ($project->isFull()) {
                return redirect()->back()->withErrors([
                    'full' => "Places on project {$project->title} are all taken. Please make your choices again."
                ]);
            }
        }

        return true;
    }

    public function validNumberOfChoices($choices)
    {
        $requiredUOGChoices = ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices', 3));
        $requiredUESTCChoices = ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6));
        $uogCount = 0;
        $uestcCount = 0;

        $projects = Project::whereIn('id', array_values($choices))->get();
        foreach ($projects as $project) {
            if ($project->institution == 'UoG') {
                $uogCount ++;
            } else {
                $uestcCount ++;
            }
        }

        if ($uogCount != $requiredUOGChoices or $uestcCount != $requiredUESTCChoices) {
            return redirect()->back()->withErrors([
                'choice_number' => "You must pick {$requiredUOGChoices} University of Glasgow projects
                    and {$requiredUESTCChoices} UESTC projects."]);
        }
        return true;
    }

    public function destroy($id)
    {
        $student = User::findOrFail($id);
        $student->removeFromAcceptedProject();
        return redirect()->route('user.show', $id);
    }
}
