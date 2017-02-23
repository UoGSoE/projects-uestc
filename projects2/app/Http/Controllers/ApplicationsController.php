<?php

namespace App\Http\Controllers;

use Artisan;
use App\Project;
use Illuminate\Http\Request;

class ApplicationsController extends Controller
{
    public function enable()
    {
        Artisan::call('projects:allowapplications', ['flag' => 'yes']);
        return redirect()->back();
    }

    public function disable()
    {
        Artisan::call('projects:allowapplications', ['flag' => 'no']);
        return redirect()->back();
    }

    public function clearUnsuccessful()
    {
        Project::clearAllUnsucessfulStudents();
        return redirect()->back();
    }
}
