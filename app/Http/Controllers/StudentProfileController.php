<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class StudentProfileController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if ($request->hasFile('cv')) {
            $user->storeCV($request->cv);
        }
        $user->bio = $request->bio;
        $user->degree_type = $request->degree_type;
        $user->save();

        return redirect('/')->with('success_message', 'Profile Updated');
    }

    public function updateDegree(Request $request)
    {
        $user = $request->user();
        $user->degree_type = $request->degree_type;
        $user->save();

        return redirect('/')->with('success_message', 'Degree Updated');
    }

    public function downloadCV($id)
    {
        $student = User::findOrFail($id);

        return response()->download($student->cvPath());
    }
}
