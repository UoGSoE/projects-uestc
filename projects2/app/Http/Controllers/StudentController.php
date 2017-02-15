<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        $users = User::students()->orderBy('surname')->get();
        return view('student.index', compact('users'));
    }
}
