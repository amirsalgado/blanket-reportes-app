<?php

use Illuminate\Support\Facades\Route;
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
});

require __DIR__ . '/auth.php';
