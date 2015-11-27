<?php

namespace App;

use App\Course;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'email', 'surname', 'forenames', 'is_student', 'location_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function scopeStudents($query)
    {
        return $query->where('is_student', '=', 1);
    }

    public function scopeStaff($query)
    {
        return $query->where('is_student', '=', 0);
    }

    public function projects()
    {
        if ($this->is_student) {
            return $this->belongsToMany(Project::class, 'project_student');
        }
        return $this->hasMany(Project::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function course()
    {
        return $this->courses->first();
    }
    public function availableProjects()
    {
        $course = $this->course();
        // if ($course->location_id) {
        //     return Project::active()->forLocation($course->location_id)->orderBy('title')->get();
        // }
        return Project::active()->forLocation($course->location_id)->orderBy('title')->get();
    }

    public function assignRole(Role $role)
    {
        if ($this->hasRole($role->title)) {
            return false;
        }
        $this->roles()->save($role);
    }

    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->roles->contains('title', $roles);
        }
        return !! $roles->intersect($this->roles)->count();
    }

    public function fullName()
    {
        return $this->forenames . ' ' . $this->surname;
    }

    public function matric()
    {
        if (!$this->is_student) {
            return 'N/A';
        }
        return preg_replace('/[^0-9]+/', '', $this->username);
    }

    public function isStaff()
    {
        return ! $this->is_student;
    }
}
