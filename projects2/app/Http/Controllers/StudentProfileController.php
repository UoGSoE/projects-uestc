<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

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
        $user->save();
        return redirect('/')->with('success_message', 'Profile Updated');
    }

    public function downloadCV($id)
    {
        $student = User::findOrFail($id);
        return response()->file($student->cvPath());
    }
}
