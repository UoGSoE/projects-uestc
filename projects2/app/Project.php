<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    public function owner()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'project_student');
    }

    public function type()
    {
        return $this->hasOne(ProjectType::class, 'type_id');
    }
}
