<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use Excel;

class ExportController extends Controller
{
    public function allocations()
    {
        $sheet = Excel::create('ProjectAllocations', function ($excel) {
            $excel->sheet('Sheet1', function ($sheet) {
                $projects = Project::active()->with('owner', 'students', 'acceptedStudents', 'discipline')->orderBy('title')->get();
                $sheet->loadView('report.partials.project_list', compact('projects'));
            });
        })->store('xlsx', false, true);
        return response()->download($sheet['full'], 'allocations.xlsx');
    }
}
