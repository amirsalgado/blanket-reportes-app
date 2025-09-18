<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

#[Layout('layouts.guest')]
new class extends Component
{
    public string $password = '';

    /**
     * Confirm the user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            $this->addError('password', __('La contraseña proporcionada no coincide con tu contraseña actual.'));

            return;
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirect(
            session('url.intended', route('dashboard', absolute: false)),
            navigate: true
        );
    }
}; ?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Esta es un área segura de la aplicación. Por favor, confirma tu contraseña antes de continuar.') }}
    </div>

    <form wire:submit="confirmPassword">
        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Contraseña')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" autofocus />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button>
                {{ __('Confirmar') }}
            </x-primary-button>
        </div>
    </form>
</div>