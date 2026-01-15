{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\layouts\app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{-- ELIMINADO EL ATRIBUTO 'dark' ESTÁTICO --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('system.display.center_title') }}</title>

    {{-- Script crítico: se ejecuta ANTES de renderizar para evitar el destello blanco --}}
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/time.js', 'resources/js/modo-dark.js'])
</head>
<body class="font-sans antialiased bg-lightBg text-lightText dark:bg-darkBg dark:text-darkText min-h-screen transition-colors duration-300">
    
    <livewire:layout.navigation />

    <main class="flex-grow">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>