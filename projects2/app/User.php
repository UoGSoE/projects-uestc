<?php

namespace App;

use App\Course;
use App\EventLog;
use App\PasswordReset;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;
use Validator;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, Notifiable;

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
    protected $fillable = ['username', 'email', 'surname', 'forenames', 'is_student', 'is_admin'];

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

    /**
     * Mutator on the email field - always strip whitespace and make lower case as it's
     * used as a username for external staff
     * @param string $email
     */
    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower(trim($email));
    }

    /**
     * Used to get the first course a student belongs to.  In reality a student should only ever be on
     * one course - need confirmation from Scott/Kathleen before changing the relationship though
     * @return App\Course
     */
    public function course()
    {
        return $this->courses->first();
    }

    public function updateCourse($request)
    {
        $this->courses()->detach();
        if ($request->has('course_id') and $request->course_id) {
            $this->courses()->sync([$request->course_id]);
        }
    }

    /**
     * All available projects associated with the course this student is on
     * @return Collection
     */
    public function availableProjects()
    {
        $course = $this->course();
        if (!$course) {
            return [];
        }
        return $course->projects()->active()->orderBy('title')->get();
    }

    /**
     * Assign a role to this user
     * @param  Role   $role
     */
    public function assignRole(Role $role)
    {
        if ($this->hasRole($role->title)) {
            return false;
        }
        $this->roles()->save($role);
    }

    /**
     * Check if this user has a given role(s).
     * @param  Mixed  $roles Either a string role name, or a Role:: collection
     * @return boolean
     */
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
        // the line below is from the laracasts tutorial on authorisation but doesn't seem to be working, hence
        // the nasty foreach loop above
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

    public function isStudent()
    {
        return $this->is_student;
    }

    public function isStaff()
    {
        return ! $this->is_student;
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    /**
     * Get the project where it is the students $choice option
     * @param  integer $choice Which choice to find (ie, 1, 2, 3 etc)
     * @return App\Project
     */
    public function projectChoice($choice = 1)
    {
        return $this->projects()->wherePivot('choice', '=', $choice)->first();
    }

    public function unallocated()
    {
        return $this->projects()->wherePivot('accepted', '=', true)->count() == 0;
    }

    /**
     * Nasty brute-force of a unique username - used when importing a spreadsheet of staff
     * as they login with their email address rather than a username - but we still need a username
     * for db/null reasons (as per original (doomed) spec)
     * @param  string $initialName The initial name to try and munge into a username
     * @return string              A unique username
     */
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

    /**
     * Create or update an existing user based on data from a spreadsheet row (see UserController->updateStaff())
     * @param  array $row A row of data from the spreadsheet
     * @return Mixed      False if the data is invalid, otherwise an instance of App\User
     */
    public static function fromSpreadsheetData($row)
    {
        $email = strtolower(trim($row[1]));
        $surname = trim($row[2]);
        $forenames = trim($row[3]);
        $rules = [
            'email' => 'required|email',
            'surname' => 'required',
            'forenames' => 'required'
        ];
        if (Validator::make(['email' => $email, 'surname' => $surname, 'forenames' => $forenames], $rules)->fails()) {
            return false;
        }
        $user = static::where('email', '=', $email)->first();
        if (!$user) {
            $user = new static;
            $user->email = $email;
            // $user->username = static::generateUsername($surname . $forenames);
            $user->username = $email;
            $user->password = bcrypt(str_random(40));
        }
        $user->surname = $surname;
        $user->forenames = $forenames;
        $user->save();
        return $user;
    }

    public static function createFromForm($request)
    {
        $user = new static;
        $user->fill($request->input());
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        $user->roles()->detach();
        if ($request->roles) {
            $user->roles()->sync(array_filter($request->roles));
        }
        if ($user->is_student) {
            $user->updateCourse($request);
        }
        EventLog::log($request->user()->id, "Created new user $user->username");
        return $user;
    }

    public static function updateFromForm($request)
    {
        $user = static::findOrFail($request->id);
        $user->fill($request->input());
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        if ($request->project_id) {
            $project = Project::findOrFail($request->project_id);
            $choices = [ $request->project_id => ["choice" => 1, "accepted" => true] ];
            EventLog::log($request->user()->id, "Allocated student {$user->username} to project {$project->title}");
            $user->allocateToProjects($choices);
        }
        $user->save();
        if ($request->roles) {
            $user->roles()->sync(array_filter($request->roles));
        } else {
            $user->roles()->detach();
        }
        if ($user->is_student) {
            $user->updateCourse($request);
        }
        EventLog::log($request->user()->id, "Updated user $user->username");
        return $user;
    }

    /**
     * Syncs the students project choices
     * @param  array $choices Array of choices (see chooseProjects for instance)
     * @return true
     */
    public function allocateToProjects($choices)
    {
        $this->projects()->detach();
        $this->projects()->sync($choices);
        return true;
    }

    public function hasCV()
    {
        return $this->cv_file;
    }

    public function storeCV($cv)
    {
        $ext = $cv->guessClientExtension();
        if (!$ext) {
            $ext = $cv->getClientOriginalExtension();
        }
        $filename = $this->id . '_cv.' . $ext;
        $cv->storeAs('cvs', $filename);
        $this->cv_file = $filename;
    }

    public function deleteCV()
    {
        if (!$this->hasCV()) {
            return true;
        }
        \Storage::delete("cvs/{$this->cv_file}");
        $this->cv_file = null;
        $this->save();
    }

    public function cvPath()
    {
        return storage_path("app/cvs/{$this->cv_file}");
    }
}
