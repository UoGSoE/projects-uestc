<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\ProjectConfig;
use App\Discipline;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $required['uestc'] = ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6));
        $required['uog'] = ProjectConfig::getOption('required_choices', config('projects.uog_required_choices', 3));
        return view('report.all_students', compact('students', 'required'));
    }

    public function allStaff()
    {
        $users = User::staff()->with('projects')->orderBy('surname')->get();
        return view('report.all_staff', compact('users'));
    }
}
