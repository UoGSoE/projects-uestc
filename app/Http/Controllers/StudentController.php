<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Project;
use App\Models\ProjectConfig;
use App\Models\User;
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
