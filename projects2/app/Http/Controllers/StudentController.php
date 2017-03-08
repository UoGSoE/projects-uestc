<?php

namespace App\Http\Controllers;

use App\User;
use App\Course;
use App\Project;
use App\ProjectConfig;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $users = User::students()->orderBy('surname')->get();
        $requiredChoices = ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices'));
        return view('student.index', compact('users', 'requiredChoices'));
    }

    public function create()
    {
        $user = new User(['is_student' => true]);
        $courses = Course::orderBy('title')->get();
        $projects = Project::active()->orderBy('title')->get();
        return view('user.create', compact('user', 'courses', 'projects'));
    }
}
