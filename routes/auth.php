<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // --- RUTA DE LOGIN---    
    Route::view('login', 'livewire.pages.auth.login-page')->name('login');

    Route::view('register', function() {
        return view('auth.register');
    })->name('register');

    Route::get('forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('forgot-password', function () {
        // Lógica de forgot password...
    })->name('password.email');

    Route::get('reset-password/{token}', function ($token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');

    Route::post('reset-password', function () {
        // Lógica de reset password...
    })->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('logout', function () {
        auth()->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});
