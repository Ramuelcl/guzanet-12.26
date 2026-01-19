{{-- resources/views/livewire/components/forms/input-file.blade.php --}}
<?php
// C:\laragon\www\laravel\guzanet-12.26\resources\views\livewire\components\forms\input-file.blade.php

use function Livewire\Volt\{state, usesFileUploads, updated};

usesFileUploads();

state([
    'upload' => null, // Variable temporal de subida
    'label' => 'Seleccionar Extracto PDF'
]);

// PROACTIVO: En cuanto 'upload' cambia, enviamos el objeto al padre
updated(['upload' => function ($value) {
    if ($value) {
        // Despachamos el evento 'file-dispatched' entregando el objeto directamente
        $this->dispatch('file-dispatched', file: $value);
        // Limpiamos el input para permitir seleccionar el mismo archivo otra vez si fuera necesario
        $this->upload = null;
    }
}]);

?>

<div class="w-full" wire:key="uploader-{{ uniqid() }}">
    <label class="block text-[10px] font-black uppercase text-gray-400 mb-2 tracking-widest">{{ $label }}</label>

    <div class="relative group border-2 border-dashed border-gray-200 p-8 text-center bg-gray-50/30 hover:border-indigo-500 transition-colors"
         x-data="{ isUploading: false }"
         x-on:livewire-upload-start="isUploading = true"
         x-on:livewire-upload-finish="isUploading = false"
         x-on:livewire-upload-error="isUploading = false">
        
        {{-- Usamos wire:model simple para que el evento updated se dispare rápido --}}
        <input type="file" 
               wire:model="upload" 
               accept=".pdf"
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
        
        <div x-show="!isUploading" class="space-y-2">
            <p class="text-[10px] text-gray-500 font-bold uppercase italic">Haz clic para cargar un archivo</p>
            <p class="text-[9px] text-indigo-500 font-black uppercase">[ PDF ]</p>
        </div>

        <div x-show="isUploading" class="flex flex-col items-center justify-center">
            <svg class="animate-spin h-5 w-5 text-indigo-600 mb-2" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-[8px] font-black text-indigo-600 uppercase">Transmitiendo datos...</span>
        </div>
    </div>
</div>