<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Discipline;

class DisciplineController extends Controller
{
    public function index()
    {
        $disciplines = Discipline::orderBy('title')->get();
        return view('discipline.index', compact('disciplines'));
    }

    public function store(Request $request)
    {
        $this->validate($request, ['title' => 'required|unique:disciplines']);
        Discipline::create($request->only('title'));
        return redirect(route('discipline.index'));
    }

    public function update($id, Request $request)
    {
        $discipline = Discipline::findOrFail($id);
        $this->validate($request, [
            'title' => ['required', Rule::unique('disciplines')->ignore($id)]
        ]);
        $discipline->title = $request->title;
        $discipline->save();
        return redirect(route('discipline.index'));
    }
}
