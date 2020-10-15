<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Project;
use App\Models\ProjectConfig;
use App\Models\User;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function allProjects()
    {
        $applicationsEnabled = Project::applicationsEnabled();
        $projects = Project::with('owner', 'students', 'acceptedStudents', 'discipline')->orderBy('title')->get();
        $disciplines = Discipline::orderBy('title')->get();

        return view('report.all_projects', compact('projects', 'disciplines', 'applicationsEnabled'));
    }

    public function allProjectsOfDiscipline($disciplineId)
    {
        $applicationsEnabled = Project::applicationsEnabled();
        $projects = Project::where('discipline_id', '=', $disciplineId)
                    ->orWhereHas('disciplines', function ($query) use ($disciplineId) {
                        $query->where('disciplines.id', $disciplineId);
                    })
                    ->with('owner', 'students', 'acceptedStudents', 'discipline', 'disciplines')
                    ->orderBy('title')
                    ->get();
        $disciplines = Discipline::orderBy('title')->get();

        return view('report.all_projects', compact('projects', 'disciplines', 'applicationsEnabled'));
    }

    public function allStudents()
    {
        $students = User::students()->with('courses', 'projects')->orderBy('surname')->get();
        $singleDegreeReq = ProjectConfig::getOption('single_uog_required_choices', config('projects.single_uog_required_choices'))
                         + ProjectConfig::getOption('single_uestc_required_choices', config('projects.single_uestc_required_choices'));
        $dualDegreeReq = ProjectConfig::getOption('required_choices', config('projects.uog_required_choices'))
                       + ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices'));
        $required = $singleDegreeReq >= $dualDegreeReq ? $singleDegreeReq : $dualDegreeReq;

        return view('report.all_students', compact('students', 'required'));
    }

    public function allStaff()
    {
        $users = User::staff()->with(['projects', 'activeProjects', 'inactiveProjects'])->orderBy('surname')->get();

        return view('report.all_staff', compact('users'));
    }
}
