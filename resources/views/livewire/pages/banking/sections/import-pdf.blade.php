{{-- resources/views/livewire/pages/banking/sections/import-pdf.blade.php --}}
<?php
use App\Models\banca\Cuenta;
use App\Models\banca\Operacion;
use App\Services\BankParserService;
use function Livewire\Volt\{state, usesFileUploads};
use Smalot\PdfParser\Parser;

usesFileUploads();
state(['pdf' => null, 'reporte' => [], 'status' => 'Esperando archivo...']);

$processPdf = function (BankParserService $parserService) {
    $this->validate(['pdf' => 'required|mimes:pdf']);
    $this->status = 'Paso 1: Desbloqueando PDF...';

    $tempOut = storage_path('app/temp_clean.pdf');
    $qpdf = public_path('qpdf/qpdf.exe');
    shell_exec("\"$qpdf\" --decrypt \"" . $this->pdf->getRealPath() . "\" \"$tempOut\" 2>&1");

    try {
        $this->status = 'Paso 2: Analizando estructura de cuentas...';
        $parser = new Parser();
        $text = $parser->parseFile($tempOut)->getText();
        $extract = $parserService->parseBancas($text);

        dd($extract);
        
        unlink($tempOut);

        $this->status = 'Paso 3: Sincronizando movimientos...';
        $totalNuevos = 0;

        // Procesamos cada cuenta detectada en el array pdfExtract
        foreach (['ccp', 'livret'] as $tipo) {
            if (!empty($extract[$tipo]['detalle'])) {
                $cuenta = Cuenta::updateOrCreate(
                    ['iban' => $extract[$tipo]['detalle']['numero'] ?? 'TEMP'],
                    [
                        'tipo_cuenta' => strtoupper($tipo),
                        'saldo_anterior' => $extract[$tipo]['detalle']['saldo_inicial'],
                        'nombre_cliente' => $extract['cliente']['nombre'],
                        'banco_nombre' => $extract['cliente']['banco'],
                        'cliente_id' => $extract['cliente']['id']
                    ]
                );

                foreach ($extract[$tipo]['operaciones'] as $op) {
                    try {
                        Operacion::create([
                            'cuenta_id' => $cuenta->id,
                            'fecha_operacion' => Carbon::createFromFormat('d/m', $op['fecha'])->setYear(2016)->format('Y-m-d'),
                            'descripcion_operacion' => $op['desc'],
                            'debito' => $op['es_debito'] ? $op['monto'] : 0,
                            'credito' => !$op['es_debito'] ? $op['monto'] : 0,
                            'valor_francos' => $op['valor_frf'],
                            'hash_operacion' => sha1($cuenta->id . $op['fecha'] . $op['desc'] . $op['monto'])
                        ]);
                        $totalNuevos++;
                    } catch (\Exception $e) {}
                }
            }
        }

        $this->reporte = ['total' => $totalNuevos, 'cuentas' => 2];
        $this->status = 'Sincronización terminada.';
        $this->reset('pdf');
    } catch (\Exception $e) {
        $this->status = 'Error: ' . $e->getMessage();
    }
};
?>

<div class="bg-white dark:bg-gray-900 p-8 border border-gray-100 shadow-sm">
    <div class="mb-4">
        <span class="text-[9px] font-black uppercase text-indigo-600 italic">Estado del proceso:</span>
        <p class="text-[10px] font-mono text-gray-500 bg-gray-50 p-2 border border-dashed">{{ $status }}</p>
    </div>

    <input type="file" wire:model="pdf" class="mb-6 block w-full text-[10px]">

    <button wire:click="processPdf" 
            wire:loading.attr="disabled"
            class="w-full bg-black text-white py-4 text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 disabled:bg-gray-400 transition-all">
        <span wire:loading.remove>Sincronizar Datos</span>
        <span wire:loading>Procesando Información...</span>
    </button>
</div>