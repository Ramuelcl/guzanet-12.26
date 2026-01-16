{{-- resources/views/livewire/pages/banking/sections/import-pdf.blade.php --}}
<?php
use function Livewire\Volt\{state, usesFileUploads};
use App\Services\BankParserService;
use App\Models\banca\Cuenta;
use App\Models\banca\Operacion;
use Smalot\PdfParser\Parser;

usesFileUploads();
state(['pdf' => null, 'reporte' => [], 'isParsing' => false]);

$processPdf = function (BankParserService $parserService) {
    $this->validate(['pdf' => 'required|mimes:pdf|max:10240']);
    $this->isParsing = true;

    $qpdf = public_path('qpdf/qpdf.exe');
    $tempIn = $this->pdf->getRealPath();
    $tempOut = storage_path('app/temp_clean.pdf');
    shell_exec("\"$qpdf\" --decrypt \"$tempIn\" \"$tempOut\" 2>&1");

    try {
        $parser = new Parser();
        $text = $parser->parseFile($tempOut)->getText();
        unlink($tempOut);

        $datosCuentas = $parserService->parseLaBanquePostale($text);
        $totalNuevos = 0;

        foreach ($datosCuentas as $d) {
            $cuenta = Cuenta::updateOrCreate(
                ['iban' => $d['iban']],
                [
                    'banco_nombre' => 'LA BANQUE POSTALE',
                    'cliente_id' => 'RAFA-001',
                    'nombre_cliente' => 'RAFAEL MUNOZ ALBUERNO',
                    'direccion_cliente' => '8 CITE DES 3 BORNES, 75011 PARIS',
                    'tipo_cuenta' => $d['tipo'],
                    'saldo_anterior' => $d['saldo_anterior'],
                    'numero_cuenta' => substr($d['iban'], -11),
                    'fecha_reporte' => now(),
                    'bic' => 'PSSTFRPPPAR'
                ]
            );

            foreach ($d['movimientos'] as $m) {
                try {
                    Operacion::create([
                        'cuenta_id' => $cuenta->id,
                        'fecha_operacion' => $m['fecha'],
                        'descripcion_operacion' => $m['desc'],
                        'debito' => $m['es_debito'] ? $m['monto'] : 0,
                        'credito' => !$m['es_debito'] ? $m['monto'] : 0,
                        'valor_francos' => $m['es_debito'] ? -$m['valor_frf'] : $m['valor_frf'],
                        'hash_operacion' => sha1($cuenta->id . $m['fecha'] . $m['desc'] . $m['monto'])
                    ]);
                    $totalNuevos++;
                } catch (\Exception $e) {}
            }
        }

        $this->reporte = ['total' => $totalNuevos, 'cuentas' => count($datosCuentas)];
        $this->reset('pdf');
    } catch (\Exception $e) {
        session()->flash('error', $e->getMessage());
    }
    $this->isParsing = false;
};
?>

<div class="bg-white dark:bg-gray-900 p-8 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden">
    <h2 class="text-[11px] font-black uppercase tracking-[0.3em] text-indigo-600 mb-6 italic">Importador Inteligente Multicuenta</h2>
    
    <input type="file" wire:model="pdf" class="mb-6 block w-full text-[10px] font-mono border border-gray-100 p-2">
    
    <button wire:click="processPdf" wire:loading.attr="disabled" class="w-full bg-black text-white py-4 text-[10px] font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all">
        <span wire:loading.remove>Sincronizar Extracto</span>
        <span wire:loading>Procesando Línea a Línea...</span>
    </button>

    @if($reporte)
        <div class="mt-6 p-4 bg-indigo-50 dark:bg-gray-800 text-[10px] font-bold uppercase italic">
            Sincronización completa: {{ $reporte['total'] }} movimientos en {{ $reporte['cuentas'] }} cuentas.
        </div>
    @endif
</div>