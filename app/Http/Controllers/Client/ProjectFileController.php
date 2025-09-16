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
        Gate::authorize('view-project-file', $projectFile);
        return Storage::disk('private')->download($projectFile->file_path, $projectFile->file_name);
    }

    public function preview(ProjectFile $projectFile)
    {
        Gate::authorize('view-project-file', $projectFile);
        if (!Storage::disk('private')->exists($projectFile->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }
        return Storage::disk('private')->response($projectFile->file_path);
    }
}