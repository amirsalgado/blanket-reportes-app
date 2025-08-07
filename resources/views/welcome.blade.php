<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blanket Ingeniería S.A.S.</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts y Estilos con Vite (Estándar del proyecto) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-blanket-blue">

    <!-- Header -->
    <header class="bg-blanket-yellow py-4 shadow-md sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center px-4">
            {{-- Asegúrate de tener el logo en public/images/logo-blanket.png --}}
            <img src="{{ asset('images/logo-blanket.png') }}" alt="Logo Blanket" class="bg-white rounded-full w-16 h-16">            
        </div>
    </header>

    <!-- Hero Section con Imagen de Fondo -->
    <section class="relative text-center py-20 px-4 bg-blanket-blue text-white">
        {{-- Reemplaza esta URL con una imagen de alta calidad. Ejemplo de Unsplash. --}}
        <div class="absolute inset-0 bg-cover bg-center opacity-20" style="background-image: url('https://images.unsplash.com/photo-1504917595217-d4dc5b70221e?q=80&w=2070&auto=format&fit=crop');"></div>
        <div class="relative z-10">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Consulta tus reportes de mantenimiento</h1>
            <p class="text-lg text-gray-300 mb-8 max-w-2xl mx-auto">Accede de forma segura a tus reportes técnicos desde cualquier lugar y en cualquier momento.</p>
            <a href="{{ route('login') }}" class="bg-blanket-yellow text-blanket-blue font-bold px-8 py-3 rounded-md shadow-lg hover:scale-105 transition-transform">Acceder a la Plataforma</a>
        </div>
    </section>

    <!-- Servicios con Iconos -->
    <section class="bg-gray-50 py-16 px-4">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-12">Nuestros Servicios</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Tarjeta de Servicio 1 -->
                <div class="bg-white p-8 shadow-lg rounded-lg text-center transform hover:-translate-y-2 transition-transform">
                    <div class="mb-4 inline-block p-4 bg-blanket-yellow rounded-full">
                        {{-- Icono de Escudo (Preventivo) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blanket-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 20.944A12.02 12.02 0 0012 22a12.02 12.02 0 009-1.056h-1.334a12.01 12.01 0 00-1.334-1.056z" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Mantenimiento Preventivo</h3>
                    <p class="text-gray-600">Prolongamos la vida útil de tus equipos con inspecciones periódicas.</p>
                </div>
                <!-- Tarjeta de Servicio 2 -->
                <div class="bg-white p-8 shadow-lg rounded-lg text-center transform hover:-translate-y-2 transition-transform">
                    <div class="mb-4 inline-block p-4 bg-blanket-yellow rounded-full">
                        {{-- Icono de Llave Inglesa (Correctivo) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blanket-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Mantenimiento Correctivo</h3>
                    <p class="text-gray-600">Solucionamos fallas técnicas de manera rápida y eficaz.</p>
                </div>
                <!-- Tarjeta de Servicio 3 -->
                <div class="bg-white p-8 shadow-lg rounded-lg text-center transform hover:-translate-y-2 transition-transform">
                    <div class="mb-4 inline-block p-4 bg-blanket-yellow rounded-full">
                        {{-- Icono de Documento (Reportes) --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blanket-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Reportes Digitales</h3>
                    <p class="text-gray-600">Recibe informes detallados en formato PDF directamente desde nuestra plataforma.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacto -->
    <section class="py-16 px-4 bg-white">
        <div class="container mx-auto text-center bg-gray-50 p-10 rounded-lg">
            <h2 class="text-3xl font-bold mb-4">¿Necesitas Ayuda?</h2>
            <p class="text-lg text-gray-600 mb-6">Estamos aquí para asistirte.</p>
            <p class="mb-2 text-gray-800"><strong>Correo:</strong> contacto@blanketingenieria.com</p>
            <p class="mb-2 text-gray-800"><strong>Teléfono:</strong> +57 300 123 4567</p>
            <p class="text-gray-800"><strong>Dirección:</strong> Sincelejo, Sucre, Colombia</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-blanket-blue text-white py-6 text-center">
        <p>&copy; {{ date('Y') }} Blanket Ingeniería S.A.S. - Todos los derechos reservados</p>
    </footer>

</body>
</html>
