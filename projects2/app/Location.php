<?php

namespace App;

use App\Course;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function activeProjects()
    {
        return $this->projects()->where('is_active', '=', 1);
    }

    public static function getDefault()
    {
        return static::where('is_default', '=', 1)->first();
    }
}
