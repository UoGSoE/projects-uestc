<?php

namespace App\Http\Controllers;

use App\EventLog;
use App\User;
use Excel;
use Illuminate\Http\Request;

class StaffImportController extends Controller
{
    public function edit()
    {
        return view('user.import');
    }

    public function update(Request $request)
    {
        $newUsers = [];
        $sheet = Excel::load($request->file('file'))->get();
        $rows = $sheet->first();
        foreach ($rows as $row) {
            $newUser = User::fromSpreadsheetData($row);
            if ($newUser) {
                $newUsers[] = $newUser;
            }
        }
        if ($newUsers) {
            return view('staff.newusers', compact('newUsers'));
        }
        EventLog::log($request->user()->id, "Updated staff list");
        return redirect()->route('staff.index')->with('success_message', 'Updated staff list');
    }
}
