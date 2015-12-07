<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = ['code', 'title'];

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_student');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
