<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Blanket Ingeniería S.A.S</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased font-sans bg-gray-100">
    <div
        class="relative min-h-screen flex flex-col items-center justify-center selection:bg-blanket-yellow selection:text-blanket-blue">

        @if (Route::has('login'))
            <div class="absolute top-0 right-0 p-6 text-right">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="font-semibold text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blanket-yellow">Dashboard</a>
                @endauth
            </div>
        @endif

        <div class="w-full max-w-md p-8 mx-auto bg-white rounded-2xl shadow-lg text-center">
            <div class="flex justify-center mb-6">
                <x-application-logo class="h-20 w-auto" />
            </div>

            <h1 class="text-3xl font-bold text-blanket-blue">
                Portal de Clientes
            </h1>
            <p class="mt-2 text-gray-600">
                Bienvenido al sistema de gestión de reportes.
            </p>

            <div class="mt-8">
                <a href="{{ route('login') }}"
                    class="w-full inline-block px-4 py-3 border border-transparent text-base font-bold rounded-md text-blanket-blue bg-blanket-yellow hover:bg-blanket-yellow-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blanket-yellow transition">
                    Iniciar Sesión
                </a>
            </div>
        </div>

        <footer class="py-8 text-center text-sm text-black/50 mt-6">
            Blanket Ingeniería S.A.S &copy; {{ date('Y') }}
        </footer>
    </div>
</body>

</html>
