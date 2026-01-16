{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\components\footer.blade.php --}}

@php
  $sys = config('system');
  $footerBlocks = $sys['display']['footer_blocks'];
  $footerIdentity = "{$sys['owner']['name']} © " . date('Y') . " [{$sys['owner']['version']}]";
@endphp

<footer class="bg-lightBg dark:bg-darkBg fixed bottom-0 left-0 w-full z-50 flex items-center h-[10mm] leading-none"
  x-data="{
      darkMode: localStorage.getItem('theme') === 'dark',
      toggleTheme() {
          this.darkMode = !this.darkMode;
          if (this.darkMode) {
              document.documentElement.classList.add('dark');
              localStorage.setItem('theme', 'dark');
          } else {
              document.documentElement.classList.remove('dark');
              localStorage.setItem('theme', 'light');
          }
      }
  }">
  <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center text-[9px] font-medium tracking-tighter text-gray-600">

      {{-- BLOQUE 1: Guzanet + Año + Versión --}}
      <div class="flex-1 text-center truncate">
        {{ $footerIdentity }}
      </div>

      {{-- Bloque 2: Ubicación --}}
      @if ($footerBlocks['location'])
        <div class="flex-1 text-center truncate">
          {{ __('location') }}: {{ Route::currentRouteName() }}
        </div>
      @endif

      {{-- Bloque 3: Usuario --}}
      @if ($footerBlocks['user'])
        <div class="flex-1 text-center font-bold">
          @auth {{ auth()->user()->name }}
          @else
          {{ __('Guest') }} @endauth
        </div>
      @endif

      {{-- Bloque 4: Idioma --}}
      @if ($footerBlocks['language'])
        <div class="flex-1 text-center flex justify-center items-center space-x-1">
          <span>{{ __('Lang') }}:</span>
          <select onchange="window.location.href='/lang/'+this.value"
            class="border-none bg-transparent p-0 focus:ring-0 cursor-pointer font-black text-gray-800 text-[9px] uppercase h-auto leading-none">
            <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>EN</option>
            <option value="es" {{ app()->getLocale() == 'es' ? 'selected' : '' }}>ES</option>
            <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>FR</option>
          </select>

          {{-- Toggle Dark Mode --}}
          {{-- Botón con AlpineJS --}}
          <button @click="toggleTheme()"
            class="w-6 h-6 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 shadow-sm transition-transform active:scale-90">
            {{-- Icono Sol (Se ve solo en modo oscuro) --}}
            <img x-show="darkMode" src="{{ asset('app/images/icons/outline/sun.svg') }}" class="w-4 h-4" alt="Sun"
              x-cloak>
            {{-- Icono Luna (Se ve solo en modo claro) --}}
            <img x-show="!darkMode" src="{{ asset('app/images/icons/outline/moon.svg') }}" class="w-3.5 h-3.5"
              alt="Moon" x-cloak>
          </button>
        </div>
      @endif

      {{-- Bloque 5: Reloj y Timezone (JS Fallback) --}}
      @if ($footerBlocks['clock'])
        <span class="px-2 text-gray-300">|</span>
        <div class="flex-1 text-center font-mono flex justify-center space-x-1">
          {{-- PHP renderiza la hora inicial para evitar el "salto" visual antes de que cargue el JS --}}
          <span id="js-clock-time">{{ date('H:i') }}</span>
          <span class="text-indigo-500">({{ config('app.timezone') }})</span>
        </div>
      @endif

    </div>
  </div>
</footer>
