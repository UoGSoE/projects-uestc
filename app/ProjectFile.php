<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Storage;

class ProjectFile extends Model
{
    protected $fillable = ['original_filename', 'file_size', 'filename'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function saveToDisk($file, $filename)
    {
        $file->storeAs('projectfiles', $filename);
    }

    public function removeFromDisk()
    {
        Storage::delete("projectfiles/{$this->filename}");
    }
}
