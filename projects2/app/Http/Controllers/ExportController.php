<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\User;
use Excel;

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

    public function students()
    {
        $sheet = Excel::create('ProjectAllocations', function ($excel) {
            $excel->sheet('Sheet1', function ($sheet) {
                $students = User::students()->with('courses', 'projects')->orderBy('surname')->get();
                $excel = true;
                $sheet->loadView('report.partials.student_list', compact('students', 'excel'));
            });
        })->store('xlsx', false, true);
        return response()->download($sheet['full'], 'students.xlsx');
    }
}
