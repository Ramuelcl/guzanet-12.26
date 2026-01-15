<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Acerca de') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <h3 class="text-lg font-bold mb-4">Información del Sistema</h3>
                <p class="mb-2"><strong>Desarrollado con:</strong> Laravel 12, Livewire 3 (Volt), Tailwind CSS y Spatie.</p>
                <p class="mb-2"><strong>Base de Datos:</strong> MySQL (Laragon).</p>
                <hr class="my-4">
                <p>Este es un módulo de información general del proyecto <strong>{{ config('app.name') }}</strong>.</p>
                
                <div class="mt-6">
                    <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Volver al Panel
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>