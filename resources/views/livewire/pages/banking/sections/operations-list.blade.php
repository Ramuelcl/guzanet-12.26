{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\livewire\pages\banking\sections\operations-list.blade.php --}}
<?php

use function Livewire\Volt\{state, with, computed};
use App\Models\banca\Operacion;

state(['search' => '']);

// Propiedad computada para obtener las operaciones filtradas
$operaciones = computed(fn () => 
    Operacion::where('descripcion_operacion', 'like', '%' . $this->search . '%')
        ->orderBy('fecha_operacion', 'desc')
        ->get()
);

?>

<div class="space-y-4">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h3 class="text-sm font-black uppercase tracking-widest text-gray-800 dark:text-gray-100">Historial de Operaciones</h3>
            <p class="text-[10px] text-gray-400 uppercase font-mono">Visualización de registros extraídos del PDF</p>
        </div>
        
        {{-- Buscador Reactivo --}}
        <div class="relative w-64">
            <input type="text" wire:model.live="search" placeholder="BUSCAR MOVIMIENTO..." 
                   class="w-full bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800 text-[10px] font-bold uppercase tracking-widest rounded-sm focus:ring-indigo-600">
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm rounded-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                    <th class="p-4 text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 italic w-32">Fecha</th>
                    <th class="p-4 text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 italic">Descripción Detallada</th>
                    <th class="p-4 text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 italic text-right">Monto (€)</th>
                    <th class="p-4 text-[9px] font-black uppercase tracking-[0.2em] text-gray-400 italic text-right">Francos (FRF)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($this->operaciones as $op)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="p-4 font-mono text-[10px] text-gray-500">
                            {{ \Carbon\Carbon::parse($op->fecha_operacion)->format('d/m/Y') }}
                        </td>
                        <td class="p-4">
                            <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300 uppercase leading-tight">
                                {{ $op->descripcion_operacion }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            @if($op->debito > 0)
                                <span class="text-[11px] font-black text-red-500">-{{ number_format($op->debito, 2) }}</span>
                            @else
                                <span class="text-[11px] font-black text-green-500">+{{ number_format($op->credito, 2) }}</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <span class="text-[10px] font-mono text-gray-400">
                                {{ number_format($op->valor_francos, 2) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-[10px] font-black uppercase text-gray-400 tracking-widest italic">
                            No se han encontrado registros en la base de datos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>