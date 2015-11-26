<?php

namespace App\Http\Controllers;

use App\Role;
use App\User;
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
        return view('user.create', compact('user', 'roles'));
    }

    public function store(Request $request)
    {
        $user = new User;
        $user->fill($request->input());
        if ($user->password) {
            $user->password = bcrypt($user->password);
        }
        $user->save();
        if ($request->roles) {
            $role = Role::findOrFail($request->roles);
            $user->assignRole($role);
        }
        return redirect()->action('UserController@show', $user->id);
    }

    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $roles = Role::orderBy('label')->get();
        return view('user.edit', compact('user', 'roles'));
    }

    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->fill($request->input());
        if ($user->password) {
            $user->password = bcrypt($user->password);
        }
        $user->save();
        if ($request->roles) {
            error_log("HASROLE");
            $role = Role::findOrFail($request->roles);
            $user->assignRole($role);
        }
        return redirect()->action('UserController@show', $user->id);
    }

    public function destroy(Request $request)
    {
        $user = User::destroy($request->id);
        return redirect()->action('UserController@index');
    }
}
