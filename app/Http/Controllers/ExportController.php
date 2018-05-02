<?php

namespace App\Http\Controllers;

use Excel;
use App\User;
use App\Project;
use App\ProjectConfig;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function allocations()
    {
        $sheet = Excel::create('ProjectAllocations', function ($excel) {
            $excel->sheet('Sheet1', function ($sheet) {
                $projects = Project::active()->with('owner', 'students', 'acceptedStudents', 'discipline')->orderBy('title')->get();
                $excel = true;
                $sheet->loadView('report.partials.project_list', compact('projects', 'excel'));
            });
        })->store('xlsx', false, true);
        return response()->download($sheet['full'], 'allocations.xlsx');
    }

    public function allStudents()
    {
        $sheet = Excel::create('ProjectAllocations', function ($excel) {
            $excel->sheet('Sheet1', function ($sheet) {
                $students = User::students()->with('courses', 'projects')->orderBy('surname')->get();
                $required['uestc'] = ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6));
                $required['uog'] = ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices', 3));
                $excel = true;
                $sheet->loadView('report.partials.student_list', compact('students', 'required', 'excel'));
            });
        })->store('xlsx', false, true);
        return response()->download($sheet['full'], 'all_students.xlsx');
    }

    public function singleDegreeStudents()
    {
        $sheet = Excel::create('ProjectAllocations', function ($excel) {
            $excel->sheet('Sheet1', function ($sheet) {
                $students = User::students()->singleDegree()->with('courses', 'projects')->orderBy('surname')->get();
                $required['uestc'] = ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6));
                $required['uog'] = ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices', 3));
                $excel = true;
                $sheet->loadView('report.partials.student_list', compact('students', 'required', 'excel'));
            });
        })->store('xlsx', false, true);
        return response()->download($sheet['full'], 'single_degree_students.xlsx');
    }

    public function dualDegreeStudents()
    {
        $sheet = Excel::create('ProjectAllocations', function ($excel) {
            $excel->sheet('Sheet1', function ($sheet) {
                $students = User::students()->dualDegree()->with('courses', 'projects')->orderBy('surname')->get();
                $required['uestc'] = ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices', 6));
                $required['uog'] = ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices', 3));
                $excel = true;
                $sheet->loadView('report.partials.student_list', compact('students', 'required', 'excel'));
            });
        })->store('xlsx', false, true);
        return response()->download($sheet['full'], 'dual_degree_students.xlsx');
    }

    public function staff()
    {
        $sheet = Excel::create('ProjectAllocations', function ($excel) {
            $excel->sheet('Sheet1', function ($sheet) {
                $users = User::staff()->with('projects')->orderBy('surname')->get();
                $sheet->loadView('report.partials.staff_list', compact('users', 'excel'));
            });
        })->store('xlsx', false, true);
        return response()->download($sheet['full'], 'staff.xlsx');
    }
}
