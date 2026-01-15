{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\layouts\app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Guzanet</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">
        
        {{-- Usamos la sintaxis de componente que es más nativa en Laravel 12 --}}
        <livewire:layout.navigation /> 

        {{-- Contenido central --}}
        <main class="flex-grow">
            {{ $slot }}
        </main>

        {{-- Footer de 10mm mantenido según tu configuración --}}
        <x-footer />

    </body>
</html>