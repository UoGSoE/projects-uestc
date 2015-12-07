<?php

namespace App\Http\Controllers;

use Auth;
use App\Role;
use App\User;
use App\Project;
use App\EventLog;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('surname')->get();
        return view('user.index', compact('users'));
    }

    public function indexStaff()
    {
        $users = User::staff()->orderBy('surname')->get();
        return view('user.index_staff', compact('users'));
    }

    public function indexStudents()
    {
        $users = User::students()->orderBy('surname')->get();
        return view('user.index_students', compact('users'));
    }

    public function show($userId)
    {
        $user = User::findOrFail($userId);
        return view('user.show', compact('user'));
    }

    public function create()
    {
        $user = new User;
        $roles = Role::orderBy('label')->get();
        return view('user.create', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        $user = new User;
        $user->fill($request->input());
        if ($request->password) {
            error_log('Setting password');
            $user->password = bcrypt($request->password);
        }
        $user->save();
        if ($request->roles) {
            $user->roles()->sync(array_filter($request->roles));
        } else {
            $user->roles()->detach();
        }
        EventLog::log(Auth::user()->id, "Created new user $user->username");
        return redirect()->action('UserController@show', $user->id);
    }

    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $roles = Role::orderBy('label')->get();
        $projects = Project::active()->orderBy('title')->get();
        return view('user.edit', compact('user', 'roles', 'projects'));
    }

    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->fill($request->input());
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        if ($request->project_id) {
            $project = Project::findOrFail($request->project_id);
            $choices = [ $request->project_id => ["choice" => 1, "accepted" => true] ];
            EventLog::log(Auth::user()->id, "Allocated student {$user->username} to project {$project->title}");
            $this->allocateStudentToProjects($user, $choices);
        }
        $user->save();
        if ($request->roles) {
            $user->roles()->sync(array_filter($request->roles));
        } else {
            $user->roles()->detach();
        }
        EventLog::log(Auth::user()->id, "Updated user $user->username");
        return redirect()->action('UserController@show', $user->id);
    }

    public function destroy($userId)
    {
        $user = User::findOrFail($userId);
        EventLog::log(Auth::user()->id, "Deleted user {$user->username}");
        $user->delete();
        return redirect()->action('UserController@index');
    }

    /**
     * Store a students project choices
     * @param  Request $request
     * @return Response
     */
    public function chooseProjects(Request $request)
    {
        $student = Auth::user();
        $first = $request->first;
        $second = $request->second;
        // $third = $request->third;
        // $fourth = $request->fourth;
        // $fifth = $request->fifth;
        $choices = [
            $first => ['choice' => 1],
            $second => ['choice' => 2],
            // $third => ['choice' => 3],
            // $fourth => ['choice' => 4],
            // $fifth => ['choice' => 5],
        ];
        $this->allocateStudentToProjects($student, $choices);
        $projects = Project::whereIn('id', array_keys($choices))->lists('title')->toArray();
        EventLog::log(Auth::user()->id, "Chose projects " . implode(', ', $projects));
        return redirect()->to('/')->with('success_message', 'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.');
    }

    /**
     * Syncs the students project choices
     * @param  User $student
     * @param  array $choices Array of choices (see chooseProjects for instance)
     * @return true
     */
    private function allocateStudentToProjects($student, $choices)
    {
        $student->projects()->detach();
        $student->projects()->sync($choices);
        return true;
    }

    /**
     * Log in as a different user (mostly so you can see what they see, do their work etc)
     * @param  integer $userId The user ID to log in as
     * @return Redirect
     */
    public function logInAs($userId)
    {
        $this->authorize('login_as_user');
        $user = User::findOrFail($userId);
        EventLog::log(Auth::user()->id, "Logged in as user {$user->username}");
        Auth::loginUsingId($userId);
        return redirect()->to('/');
    }
}
