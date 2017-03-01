<?php

namespace App\Http\Controllers;

use Auth;
use Excel;
use App\User;
use App\Course;
use App\EventLog;
use App\Http\Requests;
use App\PasswordReset;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = Course::orderBy('code')->get();
        return view('course.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $course = new Course;
        return view('course.create', compact('course'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|unique:courses',
            'title' => 'required'
        ]);
        $course = new Course;
        $course->fill($request->input());
        $course->save();
        EventLog::log(Auth::user()->id, "Created course {$course->title} {$course->code}");
        return redirect()->route('course.index')->with('success_message', 'Course Saved');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $course = Course::findOrFail($id);
        return view('course.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        return view('course.edit', compact('course'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'code' => 'required|unique:courses,code,' . $id,
            'title' => 'required'
        ]);
        $course = Course::findOrFail($id);
        $course->fill($request->input());
        $course->save();
        EventLog::log(Auth::user()->id, "Updated course {$course->title} {$course->code}");
        return redirect()->route('course.index')->with('success_message', 'Course Saved');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->projects()->detach();
        $course->delete();
        EventLog::log(Auth::user()->id, "Deleted course {$course->title} {$course->code}");
        return redirect()->route('course.index')->with('success_message', 'Course Deleted');
    }


    /**
     * Update the list of students on this course via an Excel upload
     * @param  Request $request
     * @param  integer  $id      The course->id
     * @return redirect
     */
  

}
