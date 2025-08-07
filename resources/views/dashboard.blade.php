<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    @if (auth()->user()->role === 'admin')
        {{-- Para el rol 'admin', mostramos el componente de estadísticas --}}
        <livewire:admin.dashboard-stats />
    @else
        {{-- Para el rol 'cliente', mostramos el componente de sus reportes --}}
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <livewire:client.my-reports />
            </div>
        </div>
    @endif
</x-app-layout>
