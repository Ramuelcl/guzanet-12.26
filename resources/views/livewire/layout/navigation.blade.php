{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\livewire\layout\navigation.blade.php --}}

@php
    $ownerName = config('system.owner.name');
    $ownerShort = config('system.owner.short');
    $clientName = config('system.client.name');
    $clientShort = config('system.client.short');
    $centerTitle = config('system.display.center_title');
@endphp

<nav class="bg-white border-b border-gray-100 shadow-sm h-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
        <div class="grid grid-cols-3 h-full items-center">
            
            {{-- IZQUIERDA: Guzanet + Cliente --}}
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2">
                    {{-- Logo Guzanet --}}
                    <div title="{{ $ownerName }}" class="w-8 h-8 bg-indigo-600 rounded flex items-center justify-center text-white font-bold text-xs shadow-sm">
                        {{ $ownerShort }}
                    </div>
                    
                    <span class="text-gray-300 font-light">/</span>
                    
                    {{-- Logo Cliente --}}
                    <div title="{{ $clientName }}" class="w-8 h-8 border border-dashed border-gray-300 rounded flex items-center justify-center text-[8px] text-gray-400 uppercase">
                        {{ $clientShort }}
                    </div>
                </div>
            </div>

            {{-- CENTRO: Título Dinámico --}}
            <div class="text-center">
                <h1 class="text-xl font-black tracking-[0.3em] uppercase text-gray-800">
                    {{ $centerTitle }}
                </h1>
            </div>

            {{-- DERECHA: Opciones de Usuario (Login/Register/Idioma) --}}
            <div class="flex justify-end items-center space-x-6">
                {{-- Selector de Idioma --}}
                <div class="flex items-center space-x-1 text-[10px] font-bold border-r pr-4 uppercase text-gray-500">
                    <span>{{ __('Lang') }}:</span>
                    <select onchange="window.location.href='/lang/'+this.value" class="border-none bg-transparent p-0 focus:ring-0 cursor-pointer font-black text-gray-800">
                        <option value="es" {{ app()->getLocale() == 'es' ? 'selected' : '' }}>ES</option>
                        <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>EN</option>
                        <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>FR</option>
                    </select>
                </div>

                <div class="flex items-center space-x-4 uppercase text-[10px] font-bold">
                    @auth
                        <span class="text-indigo-600 tracking-tighter">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 transition">{{ __('Log Out') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="text-gray-500 hover:text-indigo-600">{{ __('Login') }}</a>
                        <a href="{{ route('register') }}" wire:navigate class="bg-gray-800 text-white px-3 py-1.5 rounded-sm hover:bg-black transition shadow-sm">{{ __('Register') }}</a>
                    @endauth
                </div>
            </div>

        </div>
    </div>
</nav>