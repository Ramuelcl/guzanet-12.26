{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\welcome.blade.php --}}
<x-app-layout>
    {{-- Slot del Header (Opcional, se muestra debajo de la nav) --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xs text-gray-400 tracking-widest">
            {{ __('Public Home Page') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                <div class="p-12 text-center">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4 tracking-tighter">
                        {{ __('Welcome to the Platform') }}
                    </h3>
                    <p class="text-gray-500 max-w-md mx-auto leading-relaxed">
                        {{ __('You are viewing the **Guzanet** interface in guest mode. The entire structure of the system window is identical to the private area.') }}
                    </p>
                    
                    @guest
                        <div class="mt-8 flex justify-center space-x-4">
                            <a href="{{ route('login') }}" wire:navigate class="px-6 py-2 bg-indigo-600 text-white text-xs font-bold uppercase rounded shadow-md hover:bg-indigo-700 transition">
                                {{ __('Login') }}
                            </a>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
