<?php

namespace App\Http\Controllers;

use Auth;
use App\Role;
use App\User;
use App\Location;
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

    public function show($userId)
    {
        $user = User::findOrFail($userId);
        return view('user.show', compact('user'));
    }

    public function create()
    {
        $user = new User;
        $roles = Role::orderBy('label')->get();
        $user->location_id = Location::getDefault()->id;
        $locations = Location::orderBy('title')->get();
        return view('user.create', compact('user', 'roles', 'locations'));
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
        return redirect()->action('UserController@show', $user->id);
    }

    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $roles = Role::orderBy('label')->get();
        $locations = Location::orderBy('title')->get();
        return view('user.edit', compact('user', 'roles', 'locations'));
    }

    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->fill($request->input());
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        if ($request->roles) {
            $user->roles()->sync(array_filter($request->roles));
        } else {
            $user->roles()->detach();
        }
        return redirect()->action('UserController@show', $user->id);
    }

    public function destroy(Request $request)
    {
        $user = User::destroy($request->id);
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
        $student->projects()->detach();
        $choices = [
            $first => ['choice' => 1],
            $second => ['choice' => 2],
            // $third => ['choice' => 3],
            // $fourth => ['choice' => 4],
            // $fifth => ['choice' => 5],
        ];
        $student->projects()->sync($choices);
        return redirect()->to('/')->with('success_message', 'Your choices have been submitted - thank you! You will get an email once you have been accepted by a member of staff.');
    }
}
