<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ApplicationsController extends Controller
{
    public function clearUnsuccessful()
    {
        Project::clearAllUnsucessfulStudents();

        return redirect()->back();
    }
}
