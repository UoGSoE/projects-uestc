<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ProjectOversubscribedException;

class Project extends Model
{
    protected $fillable = ['title', 'description', 'prereq', 'is_active', 'user_id', 'type_id', 'maximum_students', 'discipline_id'];

    public function scopeActive($query)
    {
        return $query->where('is_active', '=', 1);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'project_student')->withPivot('accepted');
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function disciplineTitle()
    {
        if (!$this->discipline_id) {
            return 'N/A';
        }
        return $this->discipline->title;
    }

    public function isAvailable()
    {
        if ($this->isFullySubscribed()) {
            return false;
        }
        if ($this->isFull()) {
            return false;
        }
        return true;
    }

    public function isFullySubscribed()
    {
        return $this->students()->count() >= config('projects.maximumAllowedToApply');
    }

    public function isFull()
    {
        return $this->acceptedStudents()->count() >= $this->maximum_students;
    }

    public function acceptStudent($student)
    {
        if (is_numeric($student)) {
            $student = User::findOrFail($student);
        }
        $this->students()->sync([$student->id => ['accepted' => true], true]);
    }

    public function acceptedStudents()
    {
        return $this->students()->wherePivot('accepted', '=', 1);
    }

    public function addStudent($student, $accepted = false)
    {
        if (!$this->isAvailable()) {
            throw new ProjectOversubscribedException;
        }

        if (is_numeric($student)) {
            $student = User::findOrFail($student);
        }
        $this->students()->sync([$student->id => ['accepted' => $accepted]], true);
    }

    public function type()
    {
        return $this->belongsTo(ProjectType::class, 'type_id');
    }

    public function programmes()
    {
        return $this->belongsToMany(Programme::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    /**
     * Mutator - just to make sure an empty string is saved as null - makes display in views easier
     * as we can use {{ $project->prereq or 'None' }}
     * @param string $prereq
     */
    public function setPrereqAttribute($prereq)
    {
        if (!$prereq) {
            $prereq = null;
        }
        $this->attributes['prereq'] = $prereq;
    }

    /**
     * Check if this project has a given programme id (not used by UESTC)
     * @param  integer  $id
     * @return boolean
     */
    public function hasProgramme($id)
    {
        return $this->programmes->contains($id);
    }

    /**
     * Check if this project is associated with a given course id
     * @param  integer  $id
     * @return boolean
     */
    public function hasCourse($id)
    {
        return $this->courses->contains($id);
    }
}
