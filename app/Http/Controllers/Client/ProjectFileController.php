<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    public function download(ProjectFile $projectFile)
    {
        // Usamos una Gate para asegurar que el cliente solo puede descargar sus propios archivos
        Gate::authorize('view', $projectFile);

        return Storage::disk('private')->download($projectFile->file_path, $projectFile->file_name);
    }
}