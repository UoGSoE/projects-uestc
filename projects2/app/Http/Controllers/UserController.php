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
        return view('user.create', compact('user'));
    }

    public function store(Request $request)
    {
    }

    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $roles = Role::orderBy('label')->get();
        return view('user.edit', compact('user', 'roles'));
    }

    public function update(Request $request)
    {
    }

    public function destroy(Request $request)
    {
        $user = User::destroy($request->id);
        return redirect()->url('/');
    }
}
