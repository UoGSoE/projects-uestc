<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Excel;
use App\User;
use App\EventLog;

class StaffImportController extends Controller
{
    public function edit()
    {
        return view('user.import');
    }

    public function update(Request $request)
    {
        $sheet = Excel::load($request->file('file'))->get();
        $rows = $sheet->first();
        foreach ($rows as $row) {
            $user = User::fromSpreadsheetData($row);
        }
        EventLog::log($request->user()->id, "Updated staff list");
        return redirect()->route('staff.index')->with('success_message', 'Updated staff list');
    }
}
