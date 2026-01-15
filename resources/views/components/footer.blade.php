{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\components\footer.blade.php --}}
@php
  $user = auth()->user();
  $isAdmin = $user && $user->hasRole('admin');
  $routeName = Route::currentRouteName();

  // Ubicación lógica dentro del sistema
  $location = match ($routeName) {
      'home' => __('Inicio'),
      'dashboard' => __('Panel'),
      'acerca-de' => __('Info'),
      default => __('Sistema'),
  };

  // Obtenemos la zona horaria configurada en el servidor (config/app.php)
  $serverTZ = config('app.timezone');

  $separator = '<span class="px-2 text-gray-300 font-light">|</span>';
@endphp

<footer
  class="bg-inherit border-t border-gray-200 fixed bottom-0 left-0 w-full z-50 flex items-center h-[10mm] leading-none"
  id="main-footer">
  <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center text-[9px] font-medium uppercase tracking-tighter text-gray-600">

      {{-- Bloque 1: Aplicación --}}
      <div class="flex-1 text-center truncate text-gray-600">
        &copy; {{ date('Y') }} {{ config('system.owner.name') }}
      </div>

      {!! $separator !!}

      {{-- Bloque 2: Ruta del Sistema --}}
      <div class="flex-1 text-center truncate">
        <span class="text-indigo-600">{{ __('Ubicación') }}:</span> {{ $location }}
      </div>

      {!! $separator !!}

      {!! $separator !!}

      {{-- Bloque 4: Idioma --}}
      <div class="flex-1 text-center">
        {{ __('Idioma') }}: {{ strtoupper(app()->getLocale()) }}
      </div>

      {!! $separator !!}

      {{-- Bloque 5: Fecha, Hora y Origen (Zona Horaria) --}}
      <div class="flex-[1.5] text-center flex justify-center items-center space-x-1 font-mono text-[9px]">
        <span id="footer-date">{{ now()->format('d/m/y') }}</span>
        <span>|</span>
        <span id="footer-time">{{ now()->format('H:i:s') }}</span>
        <span class="ml-1 text-[8px] text-indigo-500 font-bold" id="footer-tz">
          ({{ $serverTZ }})
        </span>
      </div>

    </div>
  </div>
</footer>

<script>
  /**
   * Script de actualización dinámica compatible con SPA (Turbo/Livewire)
   * Detecta la zona horaria del navegador para mayor precisión
   */
  function updateSystemClock() {
    const now = new Date();
    const dateEl = document.getElementById('footer-date');
    const timeEl = document.getElementById('footer-time');
    const tzEl = document.getElementById('footer-tz');

    if (dateEl && timeEl) {
      dateEl.innerText = now.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: '2-digit'
      });
      timeEl.innerText = now.toLocaleTimeString('es-ES', {
        hour12: false
      });
    }

    // Si quieres que muestre la zona del navegador en lugar de la del servidor:
    if (tzEl) {
      const userTZ = Intl.DateTimeFormat().resolvedOptions().timeZone;
      tzEl.innerText = `(${userTZ})`;
    }
  }

  // Ejecución inmediata y ciclo de 1s
  if (window.footerInterval) clearInterval(window.footerInterval);
  window.footerInterval = setInterval(updateSystemClock, 1000);
  updateSystemClock();
</script>

<div class="h-8"></div>
