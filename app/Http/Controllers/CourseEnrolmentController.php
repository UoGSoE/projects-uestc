<?php

namespace App\Http\Controllers;

use App\Course;
use App\EventLog;
use App\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class CourseEnrolmentController extends Controller
{
    protected $studentIds;

    public function edit($id)
    {
        $course = Course::findOrFail($id);

        return view('course.edit_students', [
            'course' => $course,
        ]);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $file = Storage::put('tmp', $request->file('file'));
        $rows = SimpleExcelReader::create(storage_path("app/{$file}"))->noHeaderRow()->getRows();
        $students = User::students()->get();
        $rows->each(function ($row) use ($students) {
            $this->studentIds[] = $this->parseExcelRow($row, $students);
        });
        $this->studentIds = array_filter($this->studentIds);    // strip out any null-like keys
        $course->students()->sync($this->studentIds);
        EventLog::log(Auth::user()->id, "Updated student list for course {$course->title} {$course->code}");

        return redirect()->route('course.show', $id);
    }

    private function parseExcelRow($row, $students)
    {
        if (! $matric = $this->validMatric($row[0])) {
            return null;
        }
        $surname = $row[1];
        $forenames = $row[2];
        if (! $this->validName($surname)) {
            return null;
        }
        if (! $this->validName($forenames)) {
            return null;
        }
        $username = $this->makeStudentUsername($matric, $surname);
        $student = $students->where('username', $username)->first();
        if (! $student) {
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
        if (! is_numeric($matric)) {
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
     * Makes a student-esque username from their matric + surname (eg 1234567a for '1234567', 'Anderson').
     * @param  string $matric  Numeric string of the matric
     * @param  string $surname Students surname
     * @return string          A sane-looking student username
     */
    private function makeStudentUsername($matric, $surname)
    {
        return $matric.substr(strtolower($surname), 0, 1);
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

        return redirect()->route('course.show', $courseId);
    }
}
