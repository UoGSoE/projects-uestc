<?php

namespace App;

use App\Course;
use App\EventLog;
use App\Notifications\StaffPasswordNotification;
use App\PasswordReset;
use App\ProjectRound;
use App\projects;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
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
    protected $fillable = [
        'username',
        'email',
        'surname',
        'forenames',
        'is_student',
        'is_admin',
        'is_convenor',
        'institution'
    ];

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

    public function scopeSingleDegree($query)
    {
        return $query->where('degree_type', 'Single');
    }

    public function scopeDualDegree($query)
    {
        return $query->where('degree_type', 'Dual');
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
            return $this->belongsToMany(Project::class, 'project_student')->withPivot(['accepted', 'preference']);
        }
        return $this->hasMany(Project::class)->with('students', 'acceptedStudents');
    }

    public function resetToken()
    {
        return $this->hasOne(PasswordReset::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_student', 'user_id');
    }

    /**
        This returns an array of fixed length (the number of required project choices)
        for use in HTML tables (most in the admin/convenor reports)
    */
    public function projectsArray($index = null)
    {
        $projectArray = [];
        foreach (range(1, ProjectConfig::getOption('required_choices', config('projects.uog_required_choices', 3)) + ProjectConfig::getOption('uestc_required_choices', config('projects.uestc_required_choices'), 6)) as $counter) {
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
        if ($request->filled('course_id') and $request->course_id) {
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

        return $course->projects()->active()->join('users', 'projects.user_id', '=', 'users.id')
            ->orderBy('users.surname')->orderBy('projects.title')->get()->unique();
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
            $popularityPercent = 100 * ($project->students()->count() / ProjectConfig::getOption('maximum_applications', config('projects.maximumAllowedToApply', 6)));
            $projectArray[] = [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'prereq' => $project->prereq,
                'chosen' => false,
                'discipline' => $project->disciplineTitle(),
                'institution' => $project->institution,
                'discipline_css' => str_slug($project->disciplineTitle()),
                'owner' => $project->owner->fullName(),
                'links' => $project->links->toArray(),
                'files' => $project->files->toArray(),
                'popularity' => $this->getPopularity($popularityPercent),
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

    public function isSingleDegree()
    {
        return $this->degree_type == 'Single';
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

    public function removeFromAcceptedProject()
    {
        $project = $this->allocatedProject();
        if (!$project) {
            return;
        }
        $project->students()->sync([$this->id => ['accepted' => false]], false);
        $project->manually_allocated = false;
        $project->save();
    }

    /**
     * Create or update an existing user based on data from a spreadsheet row (see UserController->updateStaff())
     * @param  array $row A row of data from the spreadsheet
     * @return Mixed      False if the data is invalid, otherwise an instance of App\User
     */
    public static function fromSpreadsheetData($row)
    {
        $email = strtolower(trim($row[0]));
        $surname = trim($row[1]);
        $forenames = trim($row[2]);
        $institution = trim($row[3]);
        $rules = [
            'email' => 'required|email',
            'surname' => 'required',
            'forenames' => 'required',
            'institution' => 'required'
        ];
        if (Validator::make(['email' => $email, 'surname' => $surname, 'forenames' => $forenames, 'institution' => $institution], $rules)->fails()) {
            return;
        }
        $userExists = $user = static::where('email', '=', $email)->first();
        if (!$userExists) {
            $user = new static;
            $user->email = $email;
            $user->username = $email;
        }
        $user->surname = $surname;
        $user->forenames = $forenames;
        $user->institution = $institution;
        $user->save();
        if (!$userExists) {
            return $user;
        }
    }

    public function sendPasswordEmail()
    {
        $token = PasswordReset::create([
            'user_id' => $this->id,
            'token' => strtolower(str_random(32)),
        ]);
        $this->notify(new StaffPasswordNotification($token));
        EventLog::log($this->id, 'Generated a password creation email');
    }

    public function hasPasswordReset()
    {
        if ($this->resetToken and !$this->resetToken->hasExpired()) {
            return true;
        }
        return false;
    }

    public function hasPassword()
    {
        return $this->password != null;
    }

    public function externalHasNoPassword()
    {
        return $this->usernameIsEmail() and $this->password == null;
    }

    public function usernameIsEmail()
    {
        if (preg_match('/@/', $this->username)) {
            return true;
        }
        return false;
    }

    public static function createFromForm($request)
    {
        $user = new static;
        $user->fill($request->input());
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
            $project->preAllocate($user);
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
        foreach ($choices['uestc'] as $key => $project) {
            $this->projects()->attach($project, [
                'preference' => $this->isSingleDegree() ? 1 : $key + 1
            ]);
        }
        foreach ($choices['uog'] as $key => $project) {
            $this->projects()->attach($project, [
                'preference' => $this->isSingleDegree() ? 1 : $key + 1
            ]);
        }
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
        foreach ($choices['uestc'] as $choice) {
            $this->rounds()->create(['project_id' => $choice, 'round' => $currentRound]);
        }
        foreach ($choices['uog'] as $choice) {
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

    public function getPopularity($percent)
    {
        return [
            'percent' => $percent,
            'colour' => $this->getProgressColour($percent),
            'caption' => $this->getProgressCaption($percent),
        ];
    }

    public function getProgressColour($percent)
    {
        if ($percent < 50) {
            return 'progress-bar-success';
        } elseif ($percent < 70) {
            return 'progress-bar-warning';
        } else {
            return 'progress-bar-danger';
        }
    }

    public function getProgressCaption($percent)
    {
        if ($percent < 10) {
            return '';
        } elseif ($percent < 50) {
            return 'Somewhat popular';
        } elseif ($percent < 70) {
            return 'Very popular';
        } else {
            return 'Extremely popular';
        }
    }
}
