<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
// Usamos alias para evitar conflictos de nombres en los controladores
use App\Http\Controllers\Admin\ProjectFileController as AdminProjectFileController;
use App\Http\Controllers\Client\ProjectFileController as ClientProjectFileController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Client\ReportController as ClientReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Ruta de Bienvenida
Route::view('/', 'welcome');

// Dashboard General (se redirige según el rol)
Route::view('dashboard', 'dashboard')->middleware(['auth', 'verified'])->name('dashboard');

// Perfil de Usuario
Route::view('profile', 'profile')->middleware(['auth'])->name('profile');


// --- RUTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'admin'])->group(function () {
    // Gestión de Clientes (Usuarios)
    Volt::route('/admin/clientes', 'admin.manage-clients')->name('admin.clients');
    
    // Gestión de Reportes
    Volt::route('/admin/reportes', 'admin.manage-reports')->name('admin.reports');
    Route::get('/admin/reportes/{report}/preview', [AdminReportController::class, 'preview'])->name('admin.reports.preview');

    // Gestión de Proyectos
    Volt::route('/admin/proyectos', 'admin.projects-index')->name('admin.projects.index');
    Volt::route('/admin/proyectos/{client}', 'admin.file-manager')->name('admin.projects.show');
    Route::get('/admin/proyectos/archivos/{projectFile}/preview', [AdminProjectFileController::class, 'preview'])->name('admin.projects.files.preview');
});


// --- RUTAS DE CLIENTE ---
Route::middleware(['auth'])->group(function () {
    // Vista de Proyectos para el cliente
    Volt::route('/proyectos', 'client.project-viewer')->name('client.projects.index');
    Route::get('/proyectos/archivos/{projectFile}/descargar', [ClientProjectFileController::class, 'download'])->name('client.projects.files.download');
    
    // --- RUTA AÑADIDA Y CORREGIDA ---
    // Esta es la ruta que faltaba y causaba el error.
    Route::get('/proyectos/archivos/{projectFile}/preview', [ClientProjectFileController::class, 'preview'])->name('client.projects.files.preview');

    // Acciones de Reportes para el cliente
    Route::get('/reportes/{report}/preview', [ClientReportController::class, 'preview'])->name('client.reports.preview');
    Route::get('/reportes/{report}/descargar', [ClientReportController::class, 'download'])->name('client.reports.download');
});


require __DIR__.'/auth.php';