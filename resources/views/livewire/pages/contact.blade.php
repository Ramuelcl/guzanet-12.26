{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\home\contact.blade.php --}}
<?php

use function Livewire\Volt\{state, rules, layout};

// Definimos el layout que envolverá a esta página
layout('layouts.app'); 

state([
    'name' => '',
    'email' => '',
    'subject' => 'Consulta General',
    'message' => '',
    'sent' => false
]);

rules([
    'name'    => 'required|min:3',
    'email'   => 'required|email',
    'message' => 'required|min:10',
]);

$sendMessage = function () {
    $this->validate();

    // Aquí procesarías el envío (Mail::to(...)->send(...))
    // Por ahora simulamos éxito técnico
    
    $this->sent = true;
    $this->reset(['name', 'email', 'message']);
};

?>

<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Header de la sección --}}
        <div class="mb-8 border-l-4 border-indigo-600 pl-4">
            <h2 class="text-2xl font-black uppercase tracking-tighter text-gray-800 dark:text-gray-100">
                {{ __('contact') }}
            </h2>
            <p class="text-[10px] font-mono text-gray-400 uppercase tracking-[0.3em]">Protocolo de comunicación externa</p>
        </div>

        <div class="bg-white dark:bg-darkBg border border-gray-100 dark:border-gray-800 shadow-sm rounded-sm p-8 transition-colors duration-300">
            
            @if ($sent)
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                     class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-xs font-bold uppercase tracking-widest">
                    ✔ Mensaje transmitido correctamente.
                </div>
            @endif

            <form wire:submit="sendMessage" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    {{-- Nombre --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2 italic">Identificador / Nombre</label>
                        <input type="text" wire:model="name" 
                               class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-sm focus:ring-1 focus:ring-indigo-500 rounded-sm dark:text-gray-300">
                        @error('name') <span class="text-[10px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2 italic">Dirección de enlace (Email)</label>
                        <input type="email" wire:model="email" 
                               class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-sm focus:ring-1 focus:ring-indigo-500 rounded-sm dark:text-gray-300">
                        @error('email') <span class="text-[10px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Asunto --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2 italic">Prioridad de asunto</label>
                    <select wire:model="subject" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-sm focus:ring-1 focus:ring-indigo-500 rounded-sm dark:text-gray-300 uppercase font-bold text-[9px]">
                        <option>Consulta General</option>
                        <option>Soporte Técnico</option>
                        <option>Banca / Finanzas</option>
                        <option>Recursos Humanos</option>
                    </select>
                </div>

                {{-- Mensaje --}}
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 mb-2 italic">Cuerpo del mensaje</label>
                    <textarea wire:model="message" rows="5" 
                              class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-sm focus:ring-1 focus:ring-indigo-500 rounded-sm dark:text-gray-300"></textarea>
                    @error('message') <span class="text-[10px] text-red-500 font-bold uppercase mt-1">{{ $message }}</span> @enderror
                </div>

                {{-- Botón de envío --}}
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-black dark:hover:bg-indigo-500 text-white text-[11px] font-black uppercase tracking-[0.2em] px-10 py-3 rounded-sm transition-all shadow-lg active:scale-95 disabled:opacity-50"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Ejecutar Envío</span>
                        <span wire:loading>Procesando...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>