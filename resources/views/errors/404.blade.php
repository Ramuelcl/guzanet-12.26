<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ramuel's Systems - Página no encontrada - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans">
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100">
        <div class="text-center">
            <h1 class="text-9xl font-bold text-indigo-600">404</h1>
            <p class="text-2xl font-semibold text-gray-800 mt-4">¡Ups! Página no encontrada</p>
            <p class="text-gray-600 mt-2 mb-8">Lo sentimos, la página que estás buscando no existe o ha sido movida.</p>
            
            <a href="{{ url('/') }}" wire:navigate class="px-6 py-3 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition duration-300 shadow-lg">
                Volver al inicio
            </a>
        </div>
        
        <div class="mt-12 text-gray-400 text-sm">
            {{ config('app.name') }} - Modo SPA Activo
        </div>
    </div>
</body>
</html>