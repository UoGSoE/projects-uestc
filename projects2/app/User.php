<?php

namespace App;

use App\Course;
use App\EventLog;
use App\PasswordReset;
use App\ProjectRound;
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
    protected $fillable = ['username', 'email', 'surname', 'forenames', 'is_student', 'is_admin', 'is_convenor'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public static function boot()
    {
        parent::boot();
        
        User::deleting(function ($user) {
            foreach ($user->rounds as $round) {
                $round->delete();
            }
            if ($user->isStudent()) {
                $user->projects()->detach();
            } else {
                foreach ($user->projects as $project) {
                    $project->delete();
                }
            }
        });
    }

    public function scopeStudents($query)
    {
        return $query->where('is_student', '=', 1);
    }

    public function scopeStaff($query)
    {
        return $query->where('is_student', '=', 0);
    }

    public function rounds()
    {
        return $this->hasMany(ProjectRound::class);
    }

    public function projects()
    {
        if ($this->is_student) {
            return $this->belongsToMany(Project::class, 'project_student')->withPivot('accepted');
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

    /**
        This returns an array of fixed length (the number of required project choices)
        for use in HTML tables (most in the admin/convenor reports)
    */
    public function projectsArray($index = null)
    {
        $projectArray = [];
        foreach (range(1, ProjectConfig::getOption('required_choices', config('projects.requiredProjectChoices', 3))) as $counter) {
            $projectArray[] = null;
        }
        $projects = $this->projects()->orderBy('title')->get();
        $offset = 0;
        while ($project = $projects->shift()) {
            $projectArray[$offset] = $project;
            $offset = $offset + 1;
        }
        if (! is_null($index)) {
            return $projectArray[$index];
        }
        return $projectArray;
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
            return collect([]);
        }
        return $course->projects()->active()->inRandomOrder()->get();
    }

    /**
        Returns a JSON encoded map of the projects available to this student.
        For use in the Vue.js code where students can pick projects.
    */
    public function availableProjectsJson()
    {
        $projects = $this->availableProjects();
        $available = $projects->filter(function ($project, $key) {
            return $project->isAvailable();
        });
        $projectArray = [];
        foreach ($available as $project) {
            $projectArray[] = [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'prereq' => $project->prereq,
                'chosen' => false,
                'discipline' => $project->disciplineTitle(),
                'discipline_css' => str_slug($project->disciplineTitle()),
                'owner' => $project->owner->fullName(),
                'links' => $project->links->toArray(),
                'files' => $project->files->toArray(),
            ];
        }
        return json_encode($projectArray);
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

    public function hasRoles()
    {
        if ($this->isAdmin()) {
            return true;
        }
        if ($this->isConvenor()) {
            return true;
        }
        return false;
    }

    public function isAdmin()
    {
        return $this->is_admin;
    }

    public function isConvenor()
    {
        return $this->is_convenor;
    }

    public function unallocated()
    {
        return $this->projects()->where('accepted', '=', true)->count() == 0;
    }

    public function isAllocated()
    {
        return ! $this->unallocated();
    }

    public function allocatedProject()
    {
        return $this->projects()->where('accepted', '=', true)->first();
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
            $project->acceptStudent($user);
            EventLog::log($request->user()->id, "Allocated student {$user->username} to project {$project->title}");
        }
        $user->save();
        if ($user->is_student) {
            $user->updateCourse($request);
        }
        EventLog::log($request->user()->id, "Updated user $user->username");
        return $user;
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
        $this->save();
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

    public function allocateToProjects($choices)
    {
        $this->projects()->detach();
        $this->projects()->sync($choices);
        $this->addRoundsInfo($choices);
        return true;
    }

    protected function addRoundsInfo($choices)
    {
        $currentRound = ProjectConfig::getOption('round');
        $rounds = $this->rounds()->where('round', '=', $currentRound)->get();
        foreach ($rounds as $round) {
            $round->delete();
        }
        foreach ($choices as $choice) {
            $this->rounds()->create(['project_id' => $choice, 'round' => $currentRound]);
        }
    }

    public function roundAccept($projectId)
    {
        $currentRound = ProjectConfig::getOption('round');
        $round = $this->rounds()->where('round', '=', $currentRound)->where('project_id', '=', $projectId)->first();
        if (!$round) {
            $round = new ProjectRound;
            $round->project_id = $projectId;
            $round->user_id = $this->id;
            $round->round = $currentRound;
        }
        $round->accepted = true;
        $round->save();
    }

    public function acceptedOnRound($round)
    {
        $round = ProjectRound::where('user_id', '=', $this->id)->where('accepted', '=', true)->where('round', '=', $round)->first();
        if ($round) {
            return true;
        }
        return 0; // this is because blade template echo's false as an empty string (possibly a new bug)
    }
}
