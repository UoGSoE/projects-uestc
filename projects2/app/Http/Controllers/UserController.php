<?php

namespace App\Http\Controllers;

use App\Course;
use App\EventLog;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Project;
use App\Role;
use App\User;
use Auth;
use Excel;
use Illuminate\Http\Request;
use Validator;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UserController extends Controller
{
    public function index()
    {
        $this->authorize('edit_users');
        $users = User::orderBy('surname')->get();
        return view('user.index', compact('users'));
    }

    public function indexStaff()
    {
        $this->authorize('edit_users');
        $users = User::staff()->orderBy('surname')->get();
        return view('user.index_staff', compact('users'));
    }

    public function indexStudents()
    {
        $this->authorize('edit_users');
        $users = User::students()->orderBy('surname')->get();
        return view('user.index_students', compact('users'));
    }

    public function show($userId)
    {
        $this->authorize('view_users');
        $user = User::findOrFail($userId);
        return view('user.show', compact('user'));
    }

    public function create()
    {
        $this->authorize('edit_users');
        $user = new User;
        $roles = Role::orderBy('label')->get();
        $courses = Course::orderBy('title')->get();
        return view('user.create', compact('user', 'roles', 'courses'));
    }

    public function store(CreateUserRequest $request)
    {
        $user = User::createFromForm($request);
        return redirect()->action('UserController@show', $user->id);
    }

    public function edit($userId)
    {
        $this->authorize('edit_users');
        $user = User::findOrFail($userId);
        $roles = Role::orderBy('label')->get();
        $projects = Project::active()->orderBy('title')->get();
        $courses = Course::orderBy('title')->get();
        return view('user.edit', compact('user', 'roles', 'projects', 'courses'));
    }

    public function update(UpdateUserRequest $request)
    {
        $user = User::updateFromForm($request);
        return redirect()->action('UserController@show', $user->id);
    }

    public function destroy($userId)
    {
        $this->authorize('edit_users');
        $user = User::findOrFail($userId);
        $user->delete();
        EventLog::log(Auth::user()->id, "Deleted user {$user->username}");
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
        $picked = $request->choice;
        $requiredChoices = config('projects.requiredProjectChoices', 5);
        if (count($picked) != $requiredChoices) {
            return redirect()->back()->withErrors(['choices' => "You must pick {$requiredChoices} choices"]);
        }
        $choices = [];
        for ($i = 1; $i <= $requiredChoices; $i++) {
            $choices[$picked[$i]] = ['choice' => $i];
        }
        if (!$this->choicesAreAllDifferent($picked)) {
            return redirect()->to('/')->withErrors(['choices' => 'You must pick five *different* projects']);
        }
        $student->allocateToProjects($choices);
        $projects = Project::whereIn('id', array_keys($choices))->pluck('title')->toArray();
        EventLog::log(Auth::user()->id, "Chose projects " . implode(', ', $projects));
        return redirect()->to('/')->with(
            'success_message',
            'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.'
        );
    }

    /**
     * Make sure all the choices are different
     * @param  array $choices
     * @return boolean
     */
    private function choicesAreAllDifferent($choices)
    {
        return count($choices) == count(array_unique($choices));
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

    public function import()
    {
        $this->authorize('edit_users');
        return view('user.import');
    }

    public function updateStaff(Request $request)
    {
        $this->authorize('edit_users');
        $sheet = Excel::load($request->file('file'))->get();
        $rows = $sheet->all();
        foreach ($rows[0] as $row) {
            $user = User::fromSpreadsheetData($row);
            if (!$user) {
                abort(401);
            }
        }
        EventLog::log(Auth::user()->id, "Updated staff list");
        return redirect()->action('UserController@indexStaff')->with('success_message', 'Updated staff list');
    }
}
