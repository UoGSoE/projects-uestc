<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $users = User::staff()->orderBy('surname')->get();
        return view('staff.index', compact('users'));
    }
}
