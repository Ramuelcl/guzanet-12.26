{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\layouts\guest.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        {{-- ... meta y vite ... --}}
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-100 min-h-screen flex flex-col">
        <div class="flex-grow flex flex-col items-center pt-6 sm:pt-0">
            {{ $slot }}
        </div>

        {{-- Footer de 10mm integrado --}}
        <x-footer />
    </body>
</html>