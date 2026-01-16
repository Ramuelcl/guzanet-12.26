{{-- C:\laragon\www\laravel\guzanet-12.26\resources\views\livewire\pages\banking\sections\cuentas-list.blade.php --}}
<?php

use function Livewire\Volt\{state, computed};
use App\Models\banca\Cuenta;

// Obtenemos todas las cuentas registradas
$cuentas = computed(fn () => Cuenta::all());

?>

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($this->cuentas as $cuenta)
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 p-6 rounded-sm shadow-sm hover:border-indigo-500 transition-all group">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-600 italic">
                            {{ $cuenta->banco_nombre }}
                        </span>
                        <h4 class="text-lg font-black tracking-tighter text-gray-800 dark:text-gray-100 uppercase">
                            {{ $cuenta->tipo_cuenta }}
                        </h4>
                        <p class="text-[10px] font-mono text-gray-400">{{ $cuenta->iban }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] font-black uppercase tracking-widest text-gray-400 block mb-1">Saldo Actual</span>
                        <span class="text-xl font-black {{ $cuenta->saldo_actual >= 0 ? 'text-green-500' : 'text-red-500' }}">
                            {{ number_format($cuenta->saldo_actual, 2) }} €
                        </span>
                    </div>
                </div>

                <div class="border-t border-gray-50 dark:border-gray-800 pt-4 flex justify-between items-center">
                    <div class="text-[10px] text-gray-500 uppercase font-bold">
                        Titular: <span class="text-gray-800 dark:text-gray-300">{{ $cuenta->nombre_cliente }}</span>
                    </div>
                    <button wire:click="$parent.$set('subView', 'movimientos')" 
                            class="text-[9px] font-black uppercase tracking-widest text-indigo-600 hover:text-black dark:hover:text-white transition-colors">
                        Ver Movimientos →
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-2 py-20 text-center border-2 border-dashed border-gray-100 dark:border-gray-800">
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-300 italic">
                    No hay cuentas registradas. Importe un PDF para comenzar.
                </p>
            </div>
        @endforelse
    </div>
</div>