<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
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
        return $this->hasOne(ProjectType::class, 'type_id');
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
}
