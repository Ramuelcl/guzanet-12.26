{{-- resources/views/livewire/pages/banking/sections/import-pdf.blade.php --}}
<?php
// C:\laragon\www\laravel\guzanet-12.26\resources\views\livewire\pages\banking\sections\import-pdf.blade.php

use App\Models\banca\Cuenta;
use App\Models\banca\Operacion;
use App\Services\BankParserService;
use function Livewire\Volt\{state, usesFileUploads, updated};
use Illuminate\Support\Carbon;
use Smalot\PdfParser\Parser;

// Habilitamos la subida de archivos en este componente único
usesFileUploads();

state([
    'tempFiles' => [],    // Captura cruda del input file
    'fileQueue' => [],    // Nuestra lista de control para la UI
    'status' => 'Esperando documentos...',
    'isProcessing' => false,
    'stats' => ['procesados' => 0, 'errores' => 0]
]);

// PROACTIVO: Al seleccionar archivos, se ejecuta este hook inmediatamente
updated(['tempFiles' => function ($value) {
    // Si no hay archivos, salimos
    if (!$value) return;

    // Normalizamos a array (Livewire entrega objeto si es 1 o array si son varios)
    $uploads = is_array($value) ? $value : [$value];
    
    foreach ($uploads as $file) {
        // Evitamos duplicados en la vista comparando nombres
        $exists = collect($this->fileQueue)->contains('name', $file->getClientOriginalName());
        
        if (!$exists) {
            $this->fileQueue[] = [
                'id' => uniqid(),
                'name' => $file->getClientOriginalName(),
                'status' => 'pending', 
                'progress' => 0,
                'error' => null,
                'fileObject' => $file // Referencia directa al TemporaryUploadedFile
            ];
        }
    }
    
    $this->status = "Se han añadido " . count($uploads) . " archivo(s) a la cola.";
}]);

// Función para limpiar la lista
$clearQueue = function() {
    $this->fileQueue = [];
    $this->tempFiles = [];
    $this->status = 'Esperando documentos...';
    $this->stats = ['procesados' => 0, 'errores' => 0];
};

$processPdf = function (BankParserService $parserService) {
    if (empty($this->fileQueue)) return;

    $this->isProcessing = true;
    $qpdfPath = public_path('qpdf/qpdf.exe');

    foreach ($this->fileQueue as $index => $item) {
        // Saltamos si ya se procesó con éxito en este ciclo
        if ($item['status'] === 'success') continue;

        $this->fileQueue[$index]['status'] = 'loading';
        $this->fileQueue[$index]['progress'] = 20;
        $tempOut = storage_path('app/temp_' . uniqid() . '.pdf');

        try {
            $this->fileQueue[$index]['progress'] = 40;
            // Acceso directo al path temporal de Livewire
            $realPath = $item['fileObject']->getRealPath();
            
            shell_exec("\"$qpdfPath\" --decrypt \"$realPath\" \"$tempOut\"");

            if (!file_exists($tempOut)) throw new \Exception("QPDF: Error de descifrado.");

            $this->fileQueue[$index]['progress'] = 70;
            $text = (new Parser())->parseFile($tempOut)->getText();
            $extract = $parserService->parseBancas($text);
            
            if (file_exists($tempOut)) unlink($tempOut);

            $this->fileQueue[$index]['progress'] = 90;
            
            // Lógica de base de datos...
            foreach ($extract['cuentas'] as $cuentaData) {
                $cuenta = Cuenta::create([
                    'numero' => $cuentaData['detalle']['numero'],
                    'saldo' => $cuentaData['detalle']['saldo'],
                    'tipo' => $cuentaData['detalle']['tipo'],
                    'iban' => $cuentaData['detalle']['iban'],
                    'bic' => $cuentaData['detalle']['bic'],
                ]);
                
                foreach ($cuentaData['operaciones'] as $operacionData) {
                    Operacion::create([
                        'cuenta_id' => $cuenta->id,
                        'fecha' => $operacionData['Date'],
                        'concepto' => $operacionData['Opérations'],
                        'importe' => $operacionData['Débit'] > 0 ? $operacionData['Débit'] : $operacionData['Crédit'],
                        'tipo' => $operacionData['Débit'] > 0 ? 'debito' : 'credito',
                    ]);
                }
            }

            $this->fileQueue[$index]['status'] = 'success';
            $this->fileQueue[$index]['progress'] = 100;
            $this->stats['procesados']++;

        } catch (\Exception $e) {
            $this->fileQueue[$index]['status'] = 'error';
            $this->fileQueue[$index]['error'] = $e->getMessage();
            $this->fileQueue[$index]['progress'] = 0;
            $this->stats['errores']++;
            if (file_exists($tempOut)) unlink($tempOut);
        }
    }

    $this->isProcessing = false;
    $this->status = "Proceso terminado. Correctos: {$this->stats['procesados']}";
};
?>

<div class="bg-white dark:bg-gray-900 p-2 border border-gray-100 shadow-sm min-h-[400px]">
    
    {{-- MONITOR DE ESTADO --}}
    <div class="mb-8 flex justify-between items-start">
        <div>
            <span class="text-[10px] font-black uppercase text-indigo-600 italic tracking-[0.2em]">Sincronizador Bancario</span>
            <div class="mt-2 p-3 bg-gray-50 border border-dashed border-gray-200 min-w-[400px]">
                <p class="text-[11px] font-mono text-gray-500">>>> {{ $status }}</p>
            </div>
        </div>
        
        <div class="flex flex-col gap-2">
            {{-- BOTÓN DE ACCIÓN --}}
            @php
                $canProcess = collect($fileQueue)->where('status', '!=', 'success')->count() > 0;
            @endphp
            
            <button wire:click="processPdf" 
                    wire:loading.attr="disabled"
                    @if(!$canProcess || $isProcessing) disabled @endif
                    class="px-4 py-2 text-[9px] font-black uppercase tracking-[0.2em] transition-all
                    {{ $canProcess && !$isProcessing ? 'bg-black text-white hover:bg-indigo-700 shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                
                <div class="flex items-center justify-center gap-2">
                    <svg wire:loading wire:target="processPdf" class="animate-spin h-3 w-3 text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>
                        {{ $isProcessing ? 'Procesando...' : ($canProcess ? 'Procesar' : 'Sin archivos') }}
                    </span>
                </div>
            </button>

            @if(count($fileQueue) > 0)
                <button wire:click="clearQueue" class="text-[9px] font-black text-red-500 hover:underline uppercase">
                    Limpiar todo
                </button>
            @endif
        </div>
    </div>

    {{-- INPUT INTEGRADO (SIN COMPONENTES HIJOS) --}}
    <div class="w-full mb-8">
        <div class="relative group border-2 border-dashed border-indigo-200 p-6 text-center bg-indigo-50/20 hover:bg-indigo-50/50 transition-all cursor-pointer">
            
            {{-- Importante: multiple y wire:model deben estar aquí --}}
            <input type="file" 
                   wire:model="tempFiles" 
                   multiple 
                   accept=".pdf"
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
            
            <div class="space-y-3">
                <div class="flex justify-center text-indigo-400">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-[11px] text-gray-600 font-bold uppercase">Arrastra tus archivos PDF aquí</p>
                <p class="text-[9px] text-indigo-500 font-black uppercase tracking-widest">[ Click para seleccionar ]</p>
            </div>

            {{-- Indicador de carga de Livewire --}}
            <div wire:loading wire:target="tempFiles" class="absolute inset-0 bg-white/80 z-30 flex flex-col items-center justify-center">
                <svg class="animate-spin h-6 w-6 text-indigo-600 mb-2" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-[9px] font-black text-indigo-600 uppercase">Transmitiendo al servidor...</span>
            </div>
        </div>
    </div>

    {{-- LISTA DE ARCHIVOS DETECTADOS --}}
    @if(count($fileQueue) > 0)
        <div class="space-y-3">
            @foreach($fileQueue as $index => $file)
                <div class="p-4 border {{ $file['status'] === 'error' ? 'border-red-200 bg-red-50/30' : ($file['status'] === 'success' ? 'border-green-200 bg-green-50/20' : 'border-gray-100 bg-white shadow-sm') }} transition-all">
                    <div class="flex justify-between items-center mb-3">
                        <div class="flex items-center gap-3">
                            <span class="text-[9px] font-mono text-gray-400">#{{ $index + 1 }}</span>
                            <span class="text-[10px] font-black uppercase text-gray-800">{{ $file['name'] }}</span>
                        </div>
                        <span class="text-[8px] font-black uppercase {{ $file['status'] === 'success' ? 'text-green-600' : ($file['status'] === 'error' ? 'text-red-600' : 'text-indigo-500') }}">
                            {{ $file['status'] }}
                        </span>
                    </div>

                    {{-- Barra de progreso visual --}}
                    <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                        <div class="transition-all duration-500 h-full {{ $file['status'] === 'error' ? 'bg-red-500' : ($file['status'] === 'success' ? 'bg-green-500' : 'bg-indigo-600') }}"
                             style="width: {{ $file['progress'] }}%"></div>
                    </div>

                    @if($file['error'])
                        <p class="text-[8px] text-red-500 mt-2 font-mono italic">! {{ $file['error'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- BOTÓN DE ACCIÓN --}}
    @php
        $canProcess = collect($fileQueue)->where('status', '!=', 'success')->count() > 0;
    @endphp

    <button wire:click="processPdf" 
            wire:loading.attr="disabled"
            @if(!$canProcess || $isProcessing) disabled @endif
            class="mt-10 w-full py-5 text-[10px] font-black uppercase tracking-[0.3em] transition-all
            {{ $canProcess && !$isProcessing ? 'bg-black text-white hover:bg-indigo-700 shadow-xl' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
        
        <div class="flex items-center justify-center gap-4">
            <svg wire:loading wire:target="processPdf" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>
                {{ $isProcessing ? 'Procesando Lote...' : ($canProcess ? 'Iniciar Sincronización Automática' : 'Selecciona archivos para comenzar') }}
            </span>
        </div>
    </button>
</div>