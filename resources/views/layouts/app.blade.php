<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="flex h-screen bg-gray-100">
        <!-- Menú Lateral -->
        <div class="hidden md:flex flex-col w-64 bg-blanket-blue text-white">
            <div class="flex items-center justify-center h-20 shadow-md">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('images/logo-blanket-white.png') }}" alt="Logo Blanket" class="h-12">
                </a>
            </div>
            <div class="flex-1 flex flex-col justify-between overflow-y-auto">
                <!-- Enlaces del Menú Principal -->
                <div class="p-4">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menú</h2>

                    {{-- ENLACE COMÚN A DASHBOARD --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center mt-4 py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    {{-- --- MENÚ DEL ADMINISTRADOR --- --}}
                    @if (in_array(auth()->user()->role, ['admin', 'super-admin']))
                        <a href="{{ route('admin.reports') }}"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('admin.reports') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Reportes</span>
                        </a>

                        <a href="{{ route('admin.projects.index') }}"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('admin.projects.*') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                                </path>
                            </svg>
                            <span>Proyectos</span>
                        </a>
                        <a href="{{ route('admin.clients') }}"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('admin.clients') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Clientes</span>
                        </a>
                    @endif

                    {{-- --- MENÚ DEL CLIENTE --- --}}
                    @if (auth()->user()->role === 'cliente')
                        {{-- La vista "Mis Reportes" es el dashboard del cliente, por eso no tiene enlace propio. --}}

                        {{-- --- ENLACE AÑADIDO Y CORREGIDO --- --}}
                        <a href="{{ route('client.projects.index') }}"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('client.projects.index') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z">
                                </path>
                            </svg>
                            <span>Proyectos</span>
                        </a>
                    @endif
                </div>

                <!-- Enlaces de Perfil y Salir -->
                <div class="p-4 border-t border-blanket-blue-light">
                    <a href="{{ route('profile') }}"
                        class="flex items-center py-2 px-4 rounded-md transition duration-200 {{ request()->routeIs('profile') ? 'bg-blanket-yellow text-blanket-blue' : 'hover:bg-blanket-blue-light' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Perfil</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="flex items-center mt-2 py-2 px-4 rounded-md transition duration-200 hover:bg-blanket-blue-light">
                            <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Cerrar Sesión</span>
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-end items-center">
                    <div class="text-right">
                        <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('swal:success', event => {
            Swal.fire({
                position: 'top-end',
                icon: 'success',
                title: event.detail.message,
                showConfirmButton: false,
                timer: 2000
            })
        });
        document.addEventListener('swal:error', event => {
            Swal.fire({
                position: 'top-end',
                icon: 'error',
                title: event.detail.message,
                showConfirmButton: false,
                timer: 3000
            })
        });
    </script>
</body>

</html>
