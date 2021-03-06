<?php

namespace App\Http\Controllers;

use App\EventLog;
use App\Project;
use App\ProjectConfig;
use App\User;
use Illuminate\Http\Request;

class StudentChoicesController extends Controller
{
    public function update(Request $request)
    {
        if (!Project::applicationsEnabled()) {
            return redirect()->to('/')->withErrors(['disabled' => 'Applications are currently disabled']);
        }
        $student = $request->user();
        $choices['uestc'] = $request->uestcChoices ? explode(',', $request->uestcChoices) : [];
        $choices['uog'] = $request->uogChoices ? explode(',', $request->uogChoices) : [];
        $correctAmountOfChoices = $this->validNumberOfChoices($choices, $student);
        if ($correctAmountOfChoices !== true) {
            return $correctAmountOfChoices;
        }
        $result = $this->checkChoicesAreOk($choices);
        if ($result !== true) {
            return $result;
        }
        $student->allocateToProjects($choices);
        $uestcProjects = Project::whereIn('id', array_values($choices['uestc']))->pluck('title')->toArray();
        $uogProjects = Project::whereIn('id', array_values($choices['uog']))->pluck('title')->toArray();
        EventLog::log($student->id, "Chose projects " . implode(', ', array_merge($uestcProjects, $uogProjects)));
        return redirect()->to('/')->with(
            'success_message',
            'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.'
        );
    }

    private function choicesAreAllDifferent($choices)
    {
        return count($choices['uestc']) == count(array_unique($choices['uestc']))
            && count($choices['uog']) == count(array_unique($choices['uog']));
    }

    public function choicesHaveDifferentSupervisors($choices)
    {
        $uestcSupervisorIds = Project::find($choices['uestc'])->pluck('user_id')->all();
        $uogSupervisorIds = Project::find($choices['uog'])->pluck('user_id')->all();
        $uestcUniqueIds = array_unique($uestcSupervisorIds);
        $uogUniqueIds = array_unique($uogSupervisorIds);

        if (config('projects.uestc_unique_supervisors')) {
            if ($uestcSupervisorIds != $uestcUniqueIds) {
                return false;
            }
        }
        if (config('projects.uog_unique_supervisors')) {
            if ($uogSupervisorIds != $uogUniqueIds) {
                return false;
            }
        }
        return true;
    }

    private function checkChoicesAreOk($choices)
    {
        if (!$this->choicesAreAllDifferent($choices)) {
            return redirect()->back()->withErrors([
                'choice_diff' => "You must pick *different* projects"
            ]);
        }
        if (!$this->choicesHaveDifferentSupervisors($choices)) {
            return redirect()->back()->withErrors([
                'supervisor_diff' => "You cannot choose two projects that have the same supervisor"
            ]);
        }
        $choiceIds = array_merge($choices['uog'], $choices['uestc']);
        $projects = Project::whereIn('id', $choiceIds)->get();
        foreach ($projects as $project) {
            if ($project->isFull()) {
                return redirect()->back()->withErrors([
                    'full' => "Places on project {$project->title} are all taken. Please make your choices again."
                ]);
            }
        }

        return true;
    }

    public function validNumberOfChoices($choices, $student)
    {
        $requiredUOGChoices = $student->isSingleDegree()
                            ? ProjectConfig::getOption('single_uog_required_choices', config('projects.single_uog_required_choices'))
                            : ProjectConfig::getOption('required_choices', config('projects.uog_required_choices'));
        $requiredUESTCChoices = $student->isSingleDegree()
                              ? ProjectConfig::getOption('single_uestc_required_choices', config('projects.single_uestc_required_choices', 6))
                              : ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6));

        if (count($choices['uog']) != $requiredUOGChoices or count($choices['uestc']) != $requiredUESTCChoices) {
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
