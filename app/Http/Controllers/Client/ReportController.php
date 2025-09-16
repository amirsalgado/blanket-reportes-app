<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function preview(Report $report)
    {
        
        Gate::authorize('view-report', $report);

        if (!Storage::disk('private')->exists($report->file_path)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('private')->response($report->file_path);
    }
}