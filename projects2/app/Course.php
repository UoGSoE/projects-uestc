<?php

namespace App;

use App\User;
use App\Location;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code', 'title'];

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_student');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
