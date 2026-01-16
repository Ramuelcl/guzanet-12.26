{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\livewire\layout\navigation.blade.php --}}

@php
  // Datos Guzanet
  $ownerName = config('system.owner.name');
  $ownerLogo = config('system.owner.logo_path');

  // Datos Cliente
  $clientName = config('system.client.name');
  $clientLogo = config('system.client.logo_path');

  // Decisión de qué mostrar en el header
  $headerDisplayLogo = $clientLogo ?: $ownerLogo;
  $headerDisplayName = $clientName ?: $ownerName;
  $isWhiteLabel = !empty($clientName);
@endphp

<nav
  class="bg-lightBg dark:bg-darkBg border-b border-gray-100 dark:border-gray-800 shadow-sm h-16 transition-colors duration-300">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
    <div class="grid grid-cols-3 h-full items-center">

      {{-- IZQUIERDA: Logo y Nombre Dinámico (Prioridad Cliente) --}}
      <div class="flex items-center space-x-3">
        <a href="{{ route('home') }}" wire:navigate class="flex items-center space-x-2 group">
          <div
            class="w-8 h-8 flex items-center justify-center shadow-sm overflow-hidden">
            @if ($headerDisplayLogo)
              <img src="{{ asset($headerDisplayLogo) }}" class="h-full w-auto object-contain">
            @else
              <div
                class="w-full h-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs uppercase">
                {{ substr($headerDisplayName, 0, 1) }}
              </div>
            @endif
          </div>

          <span class="hidden md:block text-sm font-black tracking-tighter text-gray-800 uppercase">
            {{ $headerDisplayName }}
          </span>
        </a>
      </div>

      {{-- CENTRO: Título del Sistema --}}
      <div class="text-center">
        <h1 class="text-xl font-black tracking-[0.3em] uppercase text-gray-800">
          {{ config('system.display.center_title') }}
        </h1>
      </div>

      {{-- DERECHA: Opciones e Idioma --}}
      <div class="flex justify-end items-center space-x-6">

        <div class="flex items-center space-x-4 uppercase text-[10px] font-bold">
          @auth
            <span class="text-indigo-600 tracking-tighter">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
              @csrf
              <button type="submit" class="text-red-500">{{ __('Log Out') }}</button>
            </form>
          @else
            <a href="{{ route('login') }}" wire:navigate class="text-gray-500">{{ __('Login') }}</a>
            <a href="{{ route('register') }}" wire:navigate
              class="bg-gray-800 text-white px-3 py-1.5 rounded-sm">{{ __('Register') }}</a>
          @endauth
        </div>
      </div>

      {{-- FILA INFERIOR: Menú Centrado --}}
    <div class="flex justify-center items-center pb-3 space-x-8">
      <x-nav-link-custom href="/about" :active="request()->is('about')">
        {{ __('About') }}
      </x-nav-link-custom>

      <x-nav-link-custom href="/contact" :active="request()->is('contact')">
        {{ __('Contact') }}
      </x-nav-link-custom>

      <x-nav-link-custom href="/banking" :active="request()->is('banking')">
        {{ __('Banking') }}
      </x-nav-link-custom>

      <x-nav-link-custom href="/jobs" :active="request()->is('jobs')">
        {{ __('Jobs') }}
      </x-nav-link-custom>
    </div>
    
    </div>
  </div>
</nav>
