<?php

namespace App\Http\Controllers;

use Excel;
use App\User;
use App\EventLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class StaffImportController extends Controller
{
    public function edit()
    {
        return view('user.import');
    }

    public function update(Request $request)
    {
        $newUsers = [];
        $file = Storage::put('tmp', $request->file('file'));
        $rows = SimpleExcelReader::create(storage_path("app/{$file}"))->noHeaderRow()->getRows();
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
