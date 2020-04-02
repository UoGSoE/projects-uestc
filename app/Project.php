<?php

namespace App;

use App\Exceptions\ProjectOversubscribedException;
use App\Exceptions\StudentAlreadyAllocatedException;
use App\Notifications\AllocatedToProject;
use App\ProjectConfig;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Project extends Model
{
    protected $fillable = [
        'title', 'description', 'prereq', 'is_active', 'user_id', 'type_id',
        'maximum_students', 'institution', 'supervisor_name', 'supervisor_email'
    ];

    protected $casts = [
        'manually_allocated' => 'boolean'
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', '=', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', '!=', 1);
    }

    public function scopeUESTC($query)
    {
        return $query->where('institution', 'UESTC');
    }

    public function scopeUoG($query)
    {
        return $query->where('institution', 'UoG');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'project_student')->withPivot(['accepted', 'preference']);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function rounds()
    {
        return $this->hasMany(ProjectRound::class);
    }

    public function links()
    {
        return $this->hasMany(ProjectLink::class);
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, 'project_disciplines');
    }

    public function disciplineTitle()
    {
        if ($this->disciplines->count() > 0) {
            $disciplines = '';
            foreach ($this->disciplines as $discipline) {
                $disciplines = $disciplines . $discipline->title . ', ';
            }
            return substr($disciplines, 0, -2);
        }
        if (!$this->discipline_id) {
            return null;
        }
        return $this->discipline->title;
    }

    public static function applicationsEnabled()
    {
        return (bool) ProjectConfig::getOption('applications_allowed', '1');
    }

    public function isAvailable()
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->isFull()) {
            return false;
        }
        return true;
    }

    public function canAcceptAStudent()
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->isFull()) {
            return false;
        }
        return true;
    }

    public function isFullySubscribed()
    {
        $maximumAllowedToApply = ProjectConfig::getOption('maximum_applications', config('projects.maximumAllowedToApply', 6));
        return $this->students()->count() >= $maximumAllowedToApply;
    }

    public function isFull()
    {
        return $this->acceptedStudents()->count() >= $this->maximum_students;
    }


    public function acceptedStudents()
    {
        return $this->students()->where('accepted', '=', 1);
    }

    public function numberAccepted()
    {
        return $this->acceptedStudents()->count();
    }

    public function availablePlaces()
    {
        return $this->maximum_students - $this->numberAccepted();
    }

    // needs to remove students other choices!!
    public function acceptStudent($student)
    {
        if ($this->isFull()) {
            throw new ProjectOversubscribedException;
        }

        if (is_numeric($student)) {
            $student = User::findOrFail($student);
        }

        if ($student->isAllocated()) {
            throw new StudentAlreadyAllocatedException;
        }

        $this->students()->sync([$student->id => ['accepted' => true, 'preference' => null]], false);
        $student->projects()->sync([$this->id]);
        $student->notify((new AllocatedToProject($this))->delay(Carbon::now()->addSeconds(rand(10, 600))));
        $student->roundAccept($this->id);
        if ($this->isFull()) {
            $this->removeUnsucessfulStudents();
        }
    }

    public function addStudent($student, $accepted = false)
    {
        if (!$this->isAvailable()) {
            throw new ProjectOversubscribedException;
        }

        if (is_numeric($student)) {
            $student = User::findOrFail($student);
        }
        $this->students()->sync([$student->id => ['accepted' => $accepted]], false);
        $this->updateRoundsInfo($student);
    }

    public function preAllocate($student)
    {
        $this->acceptStudent($student);
        $this->manually_allocated = true;
        $this->save();
    }

    public function updateRoundsInfo($student)
    {
        $currentRound = ProjectConfig::getOption('round');
        $round = ProjectRound::where('project_id', '=', $this->id)
                    ->where('user_id', '=', $student->id)
                    ->where('round', '=', $currentRound)
                    ->first();
        if (!$round) {
            $round = new ProjectRound;
            $round->user_id = $student->id;
            $round->project_id = $this->id;
            $round->round = $currentRound;
        }
        $round->save();
        return $round;
    }

    public function roundStudentCount($roundNumber)
    {
        return $this->rounds()->where('round', '=', $roundNumber)->get()->count();
    }

    public function roundStudentAcceptedCount($roundNumber)
    {
        return $this->rounds()->where('round', '=', $roundNumber)->where('accepted', '=', true)->get()->count();
    }

    public function hasCourse($id)
    {
        return $this->courses->contains($id);
    }

    public function getDisciplineTitles()
    {
        if ($this->discipline_id) {
            return $this->discipline->title;
        }

        if ($this->disciplines->count()) {
            return $this->disciplines->pluck('title')->implode(', ');
        }

        return 'N/A';
    }

    public function syncLinks($links)
    {
        ProjectLink::where('project_id', '=', $this->id)->delete();
        foreach ($links as $link) {
            $this->addLink($link['url']);
        }
    }

    protected function addLink($url)
    {
        if (!$url) {
            return;
        }
        $this->links()->create(['url' => $url]);
    }

    public function addFiles($files)
    {
        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $size = $file->getClientSize();
            $extension = preg_replace('/[^a-z0-9]/i', '', $file->getClientOriginalExtension());
            $newName = $this->id . '/' . md5(time()) . '.' . $extension;
            $projFile = $this->files()->create([
                'original_filename' => $originalName,
                'file_size' => $size,
                'filename' => $newName
            ]);
            $projFile->saveToDisk($file, $newName);
        }
    }

    public function deleteFiles($files)
    {
        foreach ($files as $fileId) {
            $file = $this->files()->where('id', '=', $fileId)->first();
            $file->removeFromDisk();
            $file->delete();
        }
    }

    public static function clearAllUnsucessfulStudents()
    {
        $projects = static::all();
        foreach ($projects as $project) {
            $project->removeUnsucessfulStudents();
        }
    }

    public function removeUnsucessfulStudents()
    {
        $notChosen = $this->students()->where('accepted', false)->get();
        $this->students()->detach($notChosen->pluck('id')->toArray());
    }

    public function getInstitution()
    {
        if ($this->institution) {
            return $this->institution;
        }
        return Auth::user()->institution;
    }
}
