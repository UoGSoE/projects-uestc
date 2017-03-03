<?php

namespace App\Http\Controllers;

use App\User;
use App\Course;
use App\Project;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $users = User::students()->orderBy('surname')->get();
        return view('student.index', compact('users'));
    }

    public function create()
    {
        $user = new User(['is_student' => true]);
        $courses = Course::orderBy('title')->get();
        $projects = Project::active()->orderBy('title')->get();
        return view('user.create', compact('user', 'courses', 'projects'));
    }
}
