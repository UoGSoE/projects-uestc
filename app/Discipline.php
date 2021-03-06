<?php

namespace App;

use App\Project;
use Illuminate\Database\Eloquent\Model;

class Discipline extends Model
{
    protected $fillable = ['title'];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_disciplines');
    }

    public function cssTitle()
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '-', $this->title));
    }
}
