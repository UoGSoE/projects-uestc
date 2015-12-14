<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
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
        $this->validate($request, [
            'username' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'surname' => 'required',
            'forenames' => 'required'
        ]);
        $user = new User;
        $user->fill($request->input());
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        $user->roles()->detach();
        if ($request->roles) {
            $user->roles()->sync(array_filter($request->roles));
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
        $picked = $request->choice;
        $requiredChoices = config('projects.requiredProjectChoices', 5);
        if (count($picked) != $requiredChoices) {
            return redirect()->back()->withErrors(['choices' => "You must pick {$requiredChoices} choices"]);
        }
        $choices = [];
        for ($i = 1; $i <= $requiredChoices; $i++) {
            $choices[$picked[$i]] = ['choice' => $i];
        }
        // $choices = [
        //     $picked[1] => ['choice' => 1],
        //     $picked[2] => ['choice' => 2],
        //     // $third => ['choice' => 3],
        //     // $fourth => ['choice' => 4],
        //     // $fifth => ['choice' => 5],
        // ];
        if (!$this->choicesAreAllDifferent($picked)) {
            return redirect()->to('/')->withErrors(['choices' => 'You must pick five *different* projects']);
        }
        $this->allocateStudentToProjects($student, $choices);
        $projects = Project::whereIn('id', array_keys($choices))->lists('title')->toArray();
        EventLog::log(Auth::user()->id, "Chose projects " . implode(', ', $projects));
        return redirect()->to('/')->with(
            'success_message',
            'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.'
        );
    }

    private function choicesAreAllDifferent($choices)
    {
        return count($choices) == count(array_unique($choices));
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

    public function import()
    {
        return view('user.import');
    }

    public function updateStaff(Request $request)
    {
        $sheet = Excel::load($request->file('file'))->get();
        $rows = $sheet->all();
        foreach ($rows[0] as $row) {
            $email = strtolower(trim($row[0]));
            $surname = $row[1];
            $forenames = $row[2];
            if (!$this->validEmail($email)) {
                abort(401);
            }
            if (!$this->validName($surname)) {
                abort(401);
            }
            if (!$this->validName($forenames)) {
                abort(401);
            }
            $user = User::where('email', '=', $email)->first();
            if (!$user) {
                $user = new User;
                $user->email = $email;
                $user->username = User::generateUsername($surname . $forenames);
                $user->password = bcrypt(str_random(40));
            }
            $user->surname = $surname;
            $user->forenames = $forenames;
            $user->save();
        }
        EventLog::log(Auth::user()->id, "Updated staff list");
        return redirect()->action('UserController@indexStaff')->with('success_message', 'Updated staff list');
    }

    private function validName($name)
    {
        return preg_match('/[a-zA-Z]/', $name);
    }

    private function validEmail($email)
    {
        return preg_match('/[a-zA-Z0-9]+\@[a-zA-Z0-9\.]+/', $email);
    }
}
