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
        return redirect()->route('course.index');
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
        return redirect()->route('course.index');
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
        EventLog::log(Auth::user()->id, "Deleted course {$course->title} {$course->code}");
        $course->delete();
        return redirect()->action('CourseController@index');
    }

    public function editStudents($id)
    {
        $course = Course::findOrFail($id);
        return view('course.edit_students', compact('course'));
    }

    /**
     * Update the list of students on this course via an Excel upload
     * @param  Request $request
     * @param  integer  $id      The course->id
     * @return redirect
     */
    public function updateStudents(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $studentIds = [];
        $sheet = Excel::load($request->file('file'))->get();
        $rows = $sheet->first();
        $students = User::students()->get();
        foreach ($rows as $row) {
            $studentIds[] = $this->parseExcelRow($row, $students);
        }
        $studentIds = array_filter($studentIds);    // strip out any null-like keys
        $course->students()->sync($studentIds);
        EventLog::log(Auth::user()->id, "Updated student list for course {$course->title} {$course->code}");
        return redirect()->action('CourseController@show', $id);
    }

    private function parseExcelRow($row, $students)
    {
        if (! $matric = $this->validMatric($row[1])) {
            return null;
        }
        $surname = $row[2];
        $forenames = $row[3];
        if (!$this->validName($surname)) {
            return null;
        }
        if (!$this->validName($forenames)) {
            return null;
        }
        $username = $this->makeStudentUsername($matric, $surname);
        $student = $students->where('username', $username)->first();
        if (!$student) {
            $student = new User;
            $student->is_student = true;
            $student->username = $username;
            $student->surname = $surname;
            $student->forenames = $forenames;
            $student->save();
        }
        // remove any existing course associations - a student should (ha) only ever be enrolled on one
        // project course at a time.
        $student->courses()->detach();
        return $student->id;
    }

    public function validMatric($matric)
    {
        if (!is_numeric($matric)) {
            return false;
        }
        $matric = sprintf('%07d', $matric);
        if (! preg_match('/[1-9]/', $matric)) {
            // traps sprintf giving us '0000000'
            return false;
        }
        return $matric;
    }

    private function validName($name)
    {
        return preg_match('/[a-zA-Z]/', $name);
    }

    /**
     * Makes a student-esque username from their matric + surname (eg 1234567a for '1234567', 'Anderson')
     * @param  string $matric  Numeric string of the matric
     * @param  string $surname Students surname
     * @return string          A sane-looking student username
     */
    private function makeStudentUsername($matric, $surname)
    {
        return $matric . substr(strtolower($surname), 0, 1);
    }

    /**
     * Remove all the students from this course (deletes them from the DB)
     * @param  integer $courseId The course ID
     * @return redirect
     */
    public function removeStudents($courseId)
    {
        $course = Course::findOrFail($courseId);
        foreach ($course->students as $student) {
            // Note: this will remove any allocations to projects too
            $student->delete();
        }
        EventLog::log(Auth::user()->id, "Removed all students on course {$course->title} {$course->code}");
        return redirect()->action('CourseController@show', $courseId);
    }
}
