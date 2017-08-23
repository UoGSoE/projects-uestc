<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectFile;

class ProjectFileController extends Controller
{
    public function download($id)
    {
        $file = ProjectFile::findOrFail($id);
        return response()->download(storage_path("app/projectfiles/{$file->filename}"), $file->original_filename);
    }
}
