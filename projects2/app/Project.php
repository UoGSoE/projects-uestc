<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['title', 'description', 'prereq', 'is_active', 'user_id', 'type_id', 'maximum_students'];

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
        return $this->belongsToMany(User::class, 'project_student')->withPivot('choice', 'accepted');
    }

    public function isAvailable()
    {
        return $this->students()->wherePivot('accepted', '=', true)->count() < $this->maximum_students;
    }

    public function acceptedStudents()
    {
        return $this->students()->wherePivot('accepted', '=', 1);
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
