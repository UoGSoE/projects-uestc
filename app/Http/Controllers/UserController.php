<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\EventLog;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Project;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Validator;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class UserController extends Controller
{
    public function show($userId)
    {
        $user = User::findOrFail($userId);

        return view('user.show', compact('user'));
    }

    public function create()
    {
        $user = new User;
        $courses = Course::orderBy('title')->get();

        return view('user.create', compact('user', 'courses'));
    }

    public function store(CreateUserRequest $request)
    {
        $user = User::createFromForm($request);

        return redirect()->route('user.show', $user->id);
    }

    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $projects = Project::active()->orderBy('title')->get();
        $courses = Course::orderBy('title')->get();

        return view('user.edit', compact('user', 'projects', 'courses'));
    }

    public function update($id, UpdateUserRequest $request)
    {
        $user = User::updateFromForm($request);

        return redirect()->route('user.show', $user->id);
    }

    public function destroy($userId)
    {
        $user = User::findOrFail($userId);
        $redirectRoute = $this->getCorrectRedirect($user);
        $user->delete();
        EventLog::log(Auth::user()->id, "Deleted user {$user->username}");

        return redirect()->route($redirectRoute);
    }

    protected function getCorrectRedirect($user)
    {
        if ($user->isStaff()) {
            return 'staff.index';
        }

        return 'student.index';
    }

    /**
     * Log in as a different user (mostly so you can see what they see, do their work etc).
     * @param  int $userId The user ID to log in as
     * @return Redirect
     */
    public function logInAs($userId)
    {
        $user = User::findOrFail($userId);
        EventLog::log(Auth::user()->id, "Logged in as user {$user->username}");
        Auth::loginUsingId($userId);

        return redirect()->to('/');
    }
}
