<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Course;
use App\EventLog;
use App\User;
use Auth;
use Excel;

class CourseEnrolmentController extends Controller
{
    public function edit($id)
    {
        $course = Course::findOrFail($id);
        return view('course.edit_students', compact('course'));
    }

    public function update(Request $request, $id)
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
            $student->email = "{$username}@student.gla.ac.uk";
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

    public function destroy($courseId)
    {
        $course = Course::findOrFail($courseId);
        foreach ($course->students as $student) {
            $student->projects()->detach();
            $student->deleteCV();
            $student->delete();
        }
        EventLog::log(Auth::user()->id, "Removed all students on course {$course->title} {$course->code}");
        return redirect()->action('CourseController@show', $courseId);
    }
}
