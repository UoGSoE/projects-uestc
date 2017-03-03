<?php

namespace App\Http\Controllers;

use App\User;
use App\Course;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::staff()->orderBy('surname')->get();
        return view('staff.index', compact('users'));
    }

    public function create()
    {
        $user = new User(['is_staff' => true]);
        $courses = Course::orderBy('title')->get();
        return view('user.create', compact('user', 'courses'));
    }
}
