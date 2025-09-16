<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReportController;
use Livewire\Volt\Volt;


Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

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

});

require __DIR__ . '/auth.php';
