<?php

use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use function Livewire\Volt\layout;

// Es importante que el layout se defina aquí para que no use la plantilla por defecto.
layout('layouts.guest');

new class extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', trans('auth.failed'));
            return;
        }

        session()->regenerate();
        $this->redirect(
            session('url.intended', RouteServiceProvider::HOME),
            navigate: true
        );
    }
}; ?>

<div class="min-h-screen flex">
    <!-- Columna Izquierda (Branding) -->
    <div class="hidden lg:flex w-1/2 bg-blanket-blue p-12 flex-col justify-center items-center text-white relative">
        <!-- Imagen de fondo sutil -->
        <div class="absolute inset-0 bg-cover bg-center opacity-10" style="background-image: url('https://images.unsplash.com/photo-1487017159836-4e23ece2e4cf?q=80&w=2071&auto=format&fit=crop')"></div>
        
        <div class="relative z-10 text-center">
            <a href="/" class="mb-8 inline-block">
                {{-- Usamos un logo en blanco o claro para que contraste con el fondo oscuro --}}
                <img src="{{ asset('images/logo-blanket-white.png') }}" alt="Logo Blanket" class="h-24">
            </a>
            <h1 class="text-3xl font-bold mb-4">Eficiencia y Precisión en cada Reporte</h1>
            <p class="text-gray-300">Tu plataforma de confianza para la gestión de mantenimiento.</p>
        </div>
    </div>

    <!-- Columna Derecha (Formulario) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-100 p-8">
        <div class="w-full max-w-md">
            <div class="text-center lg:hidden mb-8">
                 <a href="/">
                    <x-application-logo class="w-auto h-16 mx-auto" />
                </a>
            </div>
            <h2 class="text-3xl font-bold text-blanket-blue mb-2">Bienvenido</h2>
            <p class="text-gray-600 mb-8">Por favor, inicia sesión para continuar.</p>

            <form wire:submit="login">
                <!-- Email Address -->
                <div>
                    <label for="email" class="block font-medium text-sm text-gray-700">Correo Electrónico</label>
                    <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Password -->
                <div class="mt-6">
                    <label for="password" class="block font-medium text-sm text-gray-700">Contraseña</label>
                    <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between mt-6">
                    <label for="remember" class="flex items-center">
                        <input wire:model="remember" id="remember" type="checkbox" class="rounded border-gray-300 text-blanket-blue shadow-sm focus:ring-blanket-blue" name="remember">
                        <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}" wire:navigate>
                            {{ __('¿Olvidaste tu contraseña?') }}
                        </a>
                    @endif
                </div>

                <div class="mt-8">
                    <x-primary-button class="w-full justify-center py-3 bg-blanket-yellow hover:bg-blanket-yellow-dark text-blanket-blue font-bold text-base">
                        {{ __('Iniciar Sesión') }}
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>
</div>