<?php

namespace App;

use App\Course;
use App\PasswordReset;
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
    protected $fillable = ['username', 'email', 'surname', 'forenames', 'is_student'];

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
            return $this->belongsToMany(Project::class, 'project_student')->withPivot('choice', 'accepted');
        }
        return $this->hasMany(Project::class)->with('students', 'acceptedStudents');
    }

    public function resetToken()
    {
        return $this->hasOne(PasswordReset::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student');
    }

    public function totalStudents()
    {
        $total = 0;
        foreach ($this->projects as $project) {
            $total = $total + $project->students->count();
        }
        return $total;
    }

    public function totalAcceptedStudents()
    {
        $total = 0;
        foreach ($this->projects as $project) {
            $total = $total + $project->acceptedStudents->count();
        }
        return $total;
    }

    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower(trim($email));
    }

    public function course()
    {
        return $this->courses->first();
    }

    public function availableProjects()
    {
        $course = $this->course();
        if (!$course) {
            return [];
        }
        return $course->projects()->active()->orderBy('title')->get();
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
        foreach ($roles as $role) {
            if ($this->hasRole($role->title)) {
                return true;
            }
        }
        return false;
        return !! $this->roles->intersect($roles)->count();
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

    public function projectChoice($choice = 1)
    {
        return $this->projects()->wherePivot('choice', '=', $choice)->first();
    }

    public function unallocated()
    {
        return $this->projects()->wherePivot('accepted', '=', true)->count() == 0;
    }

    public static function generateUsername($initialName)
    {
        $newName = preg_replace('/\s+/', '', $initialName);
        $suffix = 1;
        while (static::where('username', '=', $newName)->first()) {
            $newName = $newName . $suffix;
            $suffix = $suffix + 1;
            if ($suffix > 100) {    // give up after 100 tries - something is clearly up!
                abort(500);
            }
        }
        return $newName;
    }
}
