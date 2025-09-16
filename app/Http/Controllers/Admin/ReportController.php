<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Muestra el contenido de un reporte PDF en el navegador.
     *
     * @param Report $report
     * @return StreamedResponse
     */
    public function preview(Report $report)
    {
        // Verificamos que el archivo exista en el disco privado
        if (!Storage::disk('private')->exists($report->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        // Obtenemos el contenido del archivo
        $fileContents = Storage::disk('private')->get($report->file_path);

        // Devolvemos el archivo con las cabeceras correctas para que se muestre en línea
        return response($fileContents, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $report->file_name . '"',
        ]);
    }
}