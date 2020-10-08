<?php

namespace App\Http\Controllers;

use App\Course;
use App\EventLog;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\PasswordReset;
use App\ProjectConfig;
use App\User;
use Auth;
use Excel;
use Illuminate\Http\Request;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::orderBy('code')->get();

        return view('course.index', compact('courses'));
    }

    public function create()
    {
        $course = new Course;

        return view('course.create', compact('course'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|unique:courses',
            'title' => 'required',
        ]);
        $course = new Course;
        $course->fill($request->input());
        $course->save();
        EventLog::log(Auth::user()->id, "Created course {$course->title} {$course->code}");

        return redirect()->route('course.index')->with('success_message', 'Course Saved');
    }

    public function show($id)
    {
        $course = Course::findOrFail($id);
        $singleDegreeReq = ProjectConfig::getOption('single_uog_required_choices', config('projects.single_uog_required_choices'))
            + ProjectConfig::getOption('single_uestc_required_choices', config('projects.single_uestc_required_choices'));
        $dualDegreeReq = ProjectConfig::getOption('required_choices', config('projects.uog_required_choices'))
            + ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices'));
        $required = $singleDegreeReq >= $dualDegreeReq ? $singleDegreeReq : $dualDegreeReq;

        return view('course.show', compact('course', 'required'));
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);

        return view('course.edit', compact('course'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'code' => 'required|unique:courses,code,'.$id,
            'title' => 'required',
        ]);
        $course = Course::findOrFail($id);
        $course->fill($request->input());
        $course->save();
        EventLog::log(Auth::user()->id, "Updated course {$course->title} {$course->code}");

        return redirect()->route('course.index')->with('success_message', 'Course Saved');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->projects()->detach();
        $course->delete();
        EventLog::log(Auth::user()->id, "Deleted course {$course->title} {$course->code}");

        return redirect()->route('course.index')->with('success_message', 'Course Deleted');
    }
}
