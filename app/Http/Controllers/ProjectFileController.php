<?php

namespace App\Http\Controllers;

use App\Models\ProjectFile;
use Illuminate\Http\Request;

class ProjectFileController extends Controller
{
    public function download($id)
    {
        $file = ProjectFile::findOrFail($id);

        return response()->download(storage_path("app/projectfiles/{$file->filename}"), $file->original_filename);
    }
}
