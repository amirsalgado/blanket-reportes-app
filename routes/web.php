<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProjectFileController as AdminProjectFileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Client\ProjectFileController as ClientProjectFileController;
use Livewire\Volt\Volt;


Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// --- RUTAS DE CLIENTE ---
Route::middleware(['auth'])->group(function () {    
    // Vista de Proyectos para el cliente
    Volt::route('/proyectos', 'client.project-viewer')->name('client.projects.index');
    // Descarga de archivos de proyecto para el cliente
    Route::get('/proyectos/archivos/{projectFile}/descargar', [ClientProjectFileController::class, 'download'])->name('client.projects.files.download');
});

// --- NUESTRAS RUTAS DE ADMINISTRADOR ---
Route::middleware(['auth', 'admin'])->group(function () {
    // Usamos el nombre del componente Volt directamente como controlador
    Volt::route('/admin/clientes', 'admin.manage-clients')->name('admin.clients');
    Volt::route('/admin/reportes', 'admin.manage-reports')->name('admin.reports');

    // Ruta para la página de selección de cliente
    Volt::route('/admin/proyectos', 'admin.projects-index')->name('admin.projects.index');
    // Ruta para el gestor de archivos de un cliente específico
    Volt::route('/admin/proyectos/{client}', 'admin.file-manager')->name('admin.projects.show');
    // Ruta para el previsualizador de reportes
    Route::get('/admin/reportes/{report}/preview', [ReportController::class, 'preview'])->name('admin.reports.preview');
    // Ruta para el previsualizador de documentos en proyectos
    Route::get('/admin/proyectos/archivos/{projectFile}/preview', [AdminProjectFileController::class, 'preview'])->name('admin.projects.files.preview');

});

require __DIR__ . '/auth.php';
