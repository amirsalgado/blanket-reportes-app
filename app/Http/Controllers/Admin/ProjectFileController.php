<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    /**
     * Muestra el contenido de un archivo en el navegador.
     */
    public function preview(ProjectFile $projectFile)
    {
        // Verificamos que el archivo exista en el disco privado
        if (!Storage::disk('private')->exists($projectFile->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        // Devolvemos el archivo con las cabeceras correctas para que se muestre en línea
        return Storage::disk('private')->response($projectFile->file_path);
    }
}