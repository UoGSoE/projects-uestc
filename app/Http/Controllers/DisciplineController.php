<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisciplineController extends Controller
{
    public function index()
    {
        $disciplines = Discipline::orderBy('title')->get();

        return view('discipline.index', compact('disciplines'));
    }

    public function create()
    {
        $discipline = new Discipline;

        return view('discipline.create', compact('discipline'));
    }

    public function store(Request $request)
    {
        $this->validate($request, ['title' => 'required|unique:disciplines']);
        Discipline::create($request->only('title'));

        return redirect(route('discipline.index'));
    }

    public function edit($id)
    {
        $discipline = Discipline::findOrFail($id);

        return view('discipline.edit', compact('discipline'));
    }

    public function update($id, Request $request)
    {
        $discipline = Discipline::findOrFail($id);
        $this->validate($request, [
            'title' => ['required', Rule::unique('disciplines')->ignore($id)],
        ]);
        $discipline->title = $request->title;
        $discipline->save();

        return redirect(route('discipline.index'));
    }
}
