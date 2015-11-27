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

    public function scopeForLocation($query, $location_id)
    {
        return $query->where('location_id', '=', $location_id);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'project_student')->withPivot('choice', 'accepted');
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

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function hasProgramme($id)
    {
        return $this->programmes->contains($id);
    }

    public function hasCourse($id)
    {
        return $this->courses->contains($id);
    }
}
