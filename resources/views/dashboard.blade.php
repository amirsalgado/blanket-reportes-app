<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (auth()->user()->role === 'admin')
                {{-- Aquí puedes poner los KPIs del admin o un saludo --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-2xl font-semibold">Bienvenido, Administrador.</h2>
                        <p class="mt-2">Desde aquí puedes gestionar clientes y reportes usando el menú lateral.</p>
                        {{-- Aquí irían los KPIs y la gráfica --}}
                    </div>
                </div>
            @else
                {{-- Para el rol 'cliente', mostramos el componente de reportes --}}
                <livewire:client.my-reports />
            @endif
        </div>
    </div>
</x-app-layout>
