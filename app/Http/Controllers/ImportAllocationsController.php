<?php

namespace App\Http\Controllers;

use App\User;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportAllocationsController extends Controller
{
    public function index()
    {
        return view('allocations.import');
    }
    public function update(Request $request)
    {
        $this->skipped_lines = new MessageBag;
        $rows = SimpleExcelReader::create($request->file('allocations'))->noHeaderRow()->getRows();


        foreach ($rows as $lineNumber => $row) {
            $student = $this->getStudentFromRow($row, $lineNumber);
            $project = $this->getProjectFromRow($row, $lineNumber);
            if ($student and $project) {
                $project->acceptStudent($student);
            }
        };
        return redirect()->route('allocations.import')->withErrors($this->skipped_lines);
    }

    public function getStudentFromRow($row, $lineNumber)
    {
        $username = $this->stripWhiteSpace($row[0]);
        $student = User::where('username', '=', $username)->first();
        if (!$student) {
            $this->skipped_lines->add('Invalid student', "Skipped Row $lineNumber (could not find student)");
            return false;
        }
        return $student;
    }

    public function getProjectFromRow($row, $lineNumber)
    {
        $title = $row[2];
        $project = Project::where('title', '=', $title)->first();
        if (!$project) {
            $this->skipped_lines->add('Invalid project', "Skipped Row $lineNumber (could not find project)");
            return false;
        }
        if ($project->isFull()) {
            $this->skipped_lines->add('Project taken', "Skipped Row $lineNumber (project is already assigned)");
            return false;
        }
        return $project;
    }

    public function stripWhiteSpace($value)
    {
        $value = preg_replace("/\-.*/", "", $value);
        return preg_replace("/\s+/", "", $value);
    }
}
