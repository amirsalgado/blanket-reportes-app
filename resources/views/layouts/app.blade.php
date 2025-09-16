<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Blanket Reportes') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="flex">
            <!-- Menú Lateral (Sidebar) -->
            {{-- CORRECCIÓN: Se añade la clase 'relative' para posicionar correctamente los elementos hijos --}}
            <aside class="w-64 bg-blanket-blue text-white min-h-screen p-4 hidden md:block relative">
                <!-- Logo -->
                <div class="mb-8 text-center">
                    <a href="{{ route('dashboard') }}">
                        {{-- Asegúrate de tener un logo en blanco para el contraste --}}
                        <img src="{{ asset('images/logo-blanket-white.png') }}" alt="Logo Blanket" class="h-16 mx-auto">
                    </a>
                </div>

                <!-- Enlaces de Navegación -->
                <nav>
                    <h3 class="uppercase text-xs text-gray-400 font-bold mb-2">Menú</h3>
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                        {{-- Icono de Dashboard --}}
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                    @if (auth()->user()->role === 'admin')
                        <a href="{{ route('admin.reports') }}"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('admin.reports') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                            {{-- Icono de Reportes --}}
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Reportes</span>
                        </a>
                        <a href="{{ route('admin.projects.index') }}"
                            class="flex items-center py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('admin.projects.*') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-white/10' }}">
                            <svg class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                                </path>
                            </svg>
                            <span>Proyectos</span>
                        </a>
                        <a href="{{ route('admin.clients') }}"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('admin.clients') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                            {{-- Icono de Clientes --}}
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Clientes</span>
                        </a>
                    @endif
                </nav>

                <!-- Sección de Usuario y Salir -->
                {{-- CORRECCIÓN: Se eliminan las clases w-full y p-4 del contenedor. Se posiciona usando las coordenadas left, right y bottom para respetar el padding del padre --}}
                <div class="absolute bottom-4 left-4 right-4">
                    <a href="{{ route('profile') }}"
                        class="flex items-center py-2 px-4 rounded-md transition duration-200 hover:bg-blanket-blue-light">
                        {{-- Icono de Perfil/Configuración --}}
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Perfil</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 hover:bg-blanket-blue-light">
                            {{-- Icono de Salir --}}
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Cerrar Sesión</span>
                        </a>
                    </form>
                </div>
            </aside>

            <!-- Área de Contenido Principal -->
            <main class="flex-1">
                <!-- Cabecera del Contenido (opcional, para nombre de usuario, etc.) -->
                <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                    <h1 class="text-xl font-semibold text-gray-800">
                        {{-- El slot 'header' puede ser usado para poner el título de la página --}}
                        {{ $header ?? '' }}
                    </h1>
                    <div class="text-right">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </header>

                <!-- Contenido de la Página (Slot) -->
                <div class="p-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Listener para notificaciones --}}
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal:success', (event) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: event.message || '¡Acción realizada con éxito!'
                });
            });

            Livewire.on('swal:error', (event) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000, // Un poco más de tiempo para leer el error
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: 'error',
                    title: event.message || '¡Ha ocurrido un error!'
                });
            });
        });
    </script>
</body>

</html>
