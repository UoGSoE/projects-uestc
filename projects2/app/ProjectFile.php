<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectFile extends Model
{
    protected $fillable = ['original_filename', 'file_size', 'filename'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
