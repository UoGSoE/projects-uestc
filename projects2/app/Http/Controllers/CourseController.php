<?php

namespace App\Http\Controllers;

use Excel;
use App\User;
use App\Course;
use App\Location;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $locations = Location::orderBy('title')->get();
        return view('course.create', compact('course', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $course = new Course;
        $course->fill($request->input());
        if ($request->location_id) {
            $course->location_id = $request->location_id;
        }
        $course->save();
        return redirect()->action('CourseController@show', $course->id);
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
        $locations = Location::orderBy('title')->get();
        return view('course.edit', compact('course', 'locations'));
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
        $course = Course::findOrFail($id);
        $course->fill($request->input());
        if ($request->location_id) {
            $course->location_id = $request->location_id;
        }
        $course->save();
        return redirect()->action('CourseController@show', $course->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Course::destroy($id);
        return redirect()->action('CourseController@index');
    }

    public function editStudents($id)
    {
        $course = Course::findOrFail($id);
        return view('course.edit_students', compact('course'));
    }

    public function updateStudents(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $studentIds = [];
        $sheet = Excel::load($request->file('file'))->get();
        $rows = $sheet->all();
        $students = User::students()->get();
        foreach ($rows[0] as $row) {
            if (!is_numeric($row[0])) {
                continue;
            }
            $matric = sprintf('%07d', $row[0]);
            $surname = $row[1];
            $forenames = $row[2];
            if (!preg_match('/[a-zA-Z]/', $surname)) {
                continue;
            }
            if (!preg_match('/[a-zA-Z]/', $forenames)) {
                continue;
            }
            $username = $matric . substr(strtolower($surname), 0, 1);
            $student = $students->where('username', $username)->first();
            if (!$student) {
                $student = new User;
                $student->is_student = true;
                $student->username = $username;
                $student->surname = $surname;
                $student->forenames = $forenames;
                $student->email = $username . '@student.gla.ac.uk';
                $student->save();
            }
            // remove any existing course associations - a student should (ha) only ever be enrolled on one
            // project course at a time.
            $student->courses()->detach();
            $studentIds[] = $student->id;
        }
        $course->students()->sync($studentIds);
        return redirect()->action('CourseController@show', $id);
    }
}
