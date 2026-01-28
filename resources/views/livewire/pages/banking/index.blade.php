<?php
// resources/views/livewire/pages/banking/index.blade.php
use function Livewire\Volt\{state, layout};
layout('layouts.app');

state(['subView' => 'resumen']); // Controla qué pantalla mostrar a la derecha
?>

<div class="flex min-h-[calc(100vh-10mm-64px)] bg-gray-50 dark:bg-darkBg">
  {{-- MENU VERTICAL IZQUIERDO --}}
  <aside class="w-64 bg-white dark:bg-gray-900 border-r border-gray-100 dark:border-gray-800 shadow-sm">
    <div class="p-6">
      <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-600 mb-6 italic">Módulo Bancario</h2>
      <nav class="space-y-1">
        <button wire:click="$set('subView', 'importar')"
          class="w-full flex items-center px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-sm transition-all {{ $subView === 'importar' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
          <span>Importar PDF</span>
        </button>
        <button wire:click="$set('subView', 'cuentas')"
          class="w-full flex items-center px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-sm transition-all {{ $subView === 'resumen' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
          <span>Resumen de Cuentas</span>
        </button>
        <button wire:click="$set('subView', 'movimientos')"
          class="w-full flex items-center px-4 py-3 text-xs font-bold uppercase tracking-wider rounded-sm transition-all {{ $subView === 'movimientos' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-500 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
          <span>Operaciones</span>
        </button>
      </nav>
    </div>
  </aside>

  {{-- CONTENIDO DERECHO DINÁMICO --}}
  <main class="flex-1 p-8">
    @if($subView === 'importar')
      <livewire:pages.banking.sections.import-pdf />
    @elseif ($subView === 'cuentas')
      <livewire:pages.banking.sections.cuentas-list />
    @elseif($subView === 'movimientos')
      <livewire:pages.banking.sections.operations-list />
    @endif
  </main>
</div>
