<?php
// resources/views/livewire/pages/banking/sections/import-pdf.blade.php

use App\Models\banca\Cuenta;
use App\Models\banca\CuentaOperaciones;
use App\Models\backend\Entidad;
use App\Models\backend\Direccion;
use App\Models\backend\Email;
use App\Models\backend\Telefono;
use App\Services\BankParserService;
use function Livewire\Volt\{state, uses};
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

// Agregar el trait para subida de archivos
uses([WithFileUploads::class]);

state([
    'fileQueue' => [],
    'status' => 'Esperando documentos...',
    'isProcessing' => false,
    'stats' => ['procesados' => 0, 'errores' => 0],
    'archivosSeleccionados' => [],
    'isUploading' => false,
    'uploadProgress' => 0,
]);

// Función para limpiar la lista
$clearQueue = function () {
    $this->fileQueue = [];
    $this->archivosSeleccionados = [];
    $this->status = 'Esperando documentos...';
    $this->stats = ['procesados' => 0, 'errores' => 0];
    $this->isUploading = false;
    $this->uploadProgress = 0;
};

// Escuchar cuando se actualizan archivos
$updatedArchivosSeleccionados = function ($files) {
    Log::info('Archivos seleccionados: ' . count($files));

    $this->isUploading = true;
    $this->uploadProgress = 10;

    foreach ($files as $index => $file) {
        $exists = collect($this->fileQueue)->contains('name', $file->getClientOriginalName());

        if (!$exists) {
            $this->uploadProgress = 30 + $index * 20;

            $this->fileQueue[] = [
                'id' => uniqid(),
                'name' => $file->getClientOriginalName(),
                'size' => $this->formatBytes($file->getSize()),
                'type' => $file->getMimeType(),
                'status' => 'pending',
                'progress' => 0,
                'error' => null,
                'file' => $file,
                'real_path' => $file->getRealPath(),
            ];
        }
    }

    $this->uploadProgress = 100;
    $this->isUploading = false;
    $this->status = 'Se han añadido ' . count($files) . ' archivo(s) a la cola.';

    Log::info('Cola actualizada: ' . count($this->fileQueue) . ' archivos');
};

// Función para formatear bytes
$formatBytes = function ($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);

    return round($bytes, $precision) . ' ' . $units[$pow];
};

// Función para generar hash de operación
$generarHashOperacion = function (array $operacionData, int $cuentaId): string {
    $fecha = $operacionData['Date'] ?? '';
    $concepto = $operacionData['Opérations'] ?? '';
    $debito = $operacionData['Débit'] ?? 0;
    $credito = $operacionData['Crédit'] ?? 0;

    $datosUnicos = sprintf('%s|%s|%s|%s|%s', $cuentaId, $fecha, substr(md5($concepto), 0, 10), number_format($debito, 4, '.', ''), number_format($credito, 4, '.', ''));

    return md5($datosUnicos);
};

// Función para convertir fecha
// Función para convertir fecha
$convertirFecha = function(string $fechaStr, int $anioDocumento = 2000): string {
    // Limpiar y normalizar la fecha
    $fechaStr = trim($fechaStr);
    
    // Si ya está en formato MySQL, devolver tal cual
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaStr)) {
        return $fechaStr;
    }
    
    // Intentar convertir diferentes formatos
    $formatos = [
        'd/m/Y',    // 13/01/2018
        'j/n/Y',    // 13/1/2018
        'd/m',      // 13/01 (sin año)
        'j/n',      // 13/1 (sin año)
    ];
    
    foreach ($formatos as $formato) {
        $date = DateTime::createFromFormat($formato, $fechaStr);
        if ($date !== false) {
            // Si el formato no incluye año, añadirlo
            if (!strpos($formato, 'Y')) {
                $date->setDate($anioDocumento, $date->format('n'), $date->format('j'));
            }
            return $date->format('Y-m-d');
        }
    }
    
    // Si todo falla, intentar con strtotime
    $timestamp = strtotime(str_replace('/', '-', $fechaStr));
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }
    
    // Fecha por defecto si no se puede parsear
    Log::warning("No se pudo parsear la fecha: $fechaStr, usando fecha por defecto");
    return date('Y-m-d');
};

// Función para crear o encontrar una Entidad
$crearEntidad = function (array $datos, int $tipoEntidad, string $razonSocialKey = 'razonSocial', string $nombreKey = 'nombres') {
    try {
        // Determinar si es banco (tipoEntidad 16) o cliente (tipoEntidad 17)
        $esBanco = $tipoEntidad === 16;

        // Buscar por diferentes criterios según el tipo
        $query = Entidad::where('tipoEntidad', $tipoEntidad);

        if ($esBanco) {
            // Para banco, buscar por razón social o nombre
            $entidad = $query
                ->where(function ($q) use ($datos, $razonSocialKey) {
                    if (!empty($datos[$razonSocialKey])) {
                        $q->where('razonSocial', $datos[$razonSocialKey]);
                    }
                    if (!empty($datos['nombres'])) {
                        $q->orWhere('nombres', $datos['nombres']);
                    }
                })
                ->first();

            $data = [
                'tipoEntidad' => $tipoEntidad,
                'razonSocial' => $datos[$razonSocialKey] ?? null,
                'nombres' => $datos['nombres'] ?? ($datos[$razonSocialKey] ?? null),
                'is_active' => true,
            ];
        } else {
            // Para cliente, buscar por ID del cliente o nombre
            $entidad = $query
                ->where(function ($q) use ($datos, $nombreKey) {
                    if (!empty($datos['id'])) {
                        $q->where('razonSocial', $datos['id']); // Usamos razonSocial para el ID del cliente
                    }
                    if (!empty($datos[$nombreKey])) {
                        $q->orWhere('nombres', $datos[$nombreKey]);
                    }
                })
                ->first();

            $data = [
                'tipoEntidad' => $tipoEntidad,
                'razonSocial' => $datos['id'] ?? null, // ID del cliente en razonSocial
                'nombres' => $datos[$nombreKey] ?? null,
                'apellidos' => $datos['apellidos'] ?? null,
                'is_active' => true,
            ];
        }

        if (!$entidad) {
            $entidad = Entidad::create($data);
            Log::info("Entidad creada: ID {$entidad->id}, Tipo {$tipoEntidad}");
        } else {
            Log::info("Entidad encontrada: ID {$entidad->id}, Tipo {$tipoEntidad}");
        }

        return $entidad;
    } catch (\Exception $e) {
        Log::error('Error creando/actualizando entidad: ' . $e->getMessage());
        throw $e;
    }
};

// Función para agregar dirección a Entidad
$agregarDireccionEntidad = function ($entidad, array $datosDireccion) {
    try {
        // Verificar si ya existe una dirección similar
        $direccionExistente = $entidad
            ->direcciones()
            ->where('calle', $datosDireccion['calle'] ?? '')
            ->where('numero', $datosDireccion['numero'] ?? '')
            ->first();

        if (!$direccionExistente) {
            Direccion::create([
                'entidad_id' => $entidad->id,
                'numero' => $datosDireccion['numero'] ?? '',
                'calle' => $datosDireccion['calle'] ?? '',
                'tipo' => $datosDireccion['tipo'] ?? 1, // Tipo por defecto
                'cp_id' => $datosDireccion['cp_id'] ?? null,
            ]);
            Log::info("Dirección agregada a entidad ID {$entidad->id}");
        }
    } catch (\Exception $e) {
        Log::warning('Error agregando dirección: ' . $e->getMessage());
    }
};

$processPdf = function (BankParserService $parserService) {
    if (empty($this->fileQueue)) {
        return;
    }

    $this->isProcessing = true;
    $this->status = 'Procesando archivos...';
    $qpdfPath = public_path('qpdf/qpdf.exe');

    foreach ($this->fileQueue as $index => $item) {
        if ($item['status'] === 'success') {
            continue;
        }

        $this->fileQueue[$index]['status'] = 'loading';
        $this->fileQueue[$index]['progress'] = 20;
        $tempOut = storage_path('app/temp_' . uniqid() . '.pdf');

        try {
            $this->fileQueue[$index]['progress'] = 40;

            // Usar la ruta real del archivo Livewire
            $realPath = $item['real_path'];

            if (!file_exists($realPath)) {
                throw new \Exception('Archivo temporal no encontrado: ' . $item['name']);
            }

            shell_exec("\"$qpdfPath\" --decrypt \"$realPath\" \"$tempOut\"");

            if (!file_exists($tempOut)) {
                throw new \Exception('QPDF: Error de descifrado.');
            }

            $this->fileQueue[$index]['progress'] = 70;
            $parser = new Parser();
            $document = $parser->parseFile($tempOut);
            $text = $document->getText();
            $extract = $parserService->parseBancas($text);

            if (file_exists($tempOut)) {
                unlink($tempOut);
            }

            $this->fileQueue[$index]['progress'] = 90;

            // 1. Crear/Actualizar Entidad del Banco (tipoEntidad = 16)
            $datosBanco = [
                'razonSocial' => $extract['banco_nombre_real'] ?? 'Banco Desconocido',
                'nombres' => $extract['banco_nombre_real'] ?? 'Banco Desconocido',
            ];

            $entidadBanco = $this->crearEntidad($datosBanco, 16);

            // 2. Crear/Actualizar Entidad del Cliente (tipoEntidad = 17)
            $nombreCompleto = $extract['cliente']['nombre'] ?? '';
            $partesNombre = explode(' ', $nombreCompleto, 2);

            $datosCliente = [
                'id' => $extract['cliente']['id'] ?? '',
                'nombres' => $partesNombre[0] ?? '',
                'apellidos' => $partesNombre[1] ?? $nombreCompleto,
            ];

            $entidadCliente = $this->crearEntidad($datosCliente, 17, 'id', 'nombres');

            // 3. Procesar cada cuenta del extracto
            foreach ($extract['cuentas'] as $cuentaData) {
                // Buscar o crear la cuenta
                $cuenta = Cuenta::firstOrCreate(
                    ['numero_cuenta' => $cuentaData['detalle']['numero']],
                    [
                        'numero_cuenta' => $cuentaData['detalle']['numero'],
                        'saldo_actual' => $cuentaData['detalle']['saldo'] ?? 0.0,
                        'saldo_anterior' => 0.0,
                        'tipo' => $cuentaData['detalle']['tipo'] ?? 'Cuenta Corriente',
                        'iban' => $cuentaData['detalle']['iban'] ?? '',
                        'bic' => $cuentaData['detalle']['bic'] ?? '',
                        'banco_entidad_id' => $entidadBanco->id,
                        'cliente_entidad_id' => $entidadCliente->id,
                        'banco_nombre' => $extract['banco_nombre_real'] ?? 'Banco Desconocido',
                        'cliente_nombre' => $extract['cliente']['nombre'] ?? 'Cliente Desconocido',
                        'cliente_id' => $extract['cliente']['id'] ?? '',
                    ],
                );

                Log::info("Cuenta procesada: {$cuenta->id} - {$cuenta->numero_cuenta}");
                Log::info("Banco Entidad ID: {$entidadBanco->id}, Cliente Entidad ID: {$entidadCliente->id}");

                // 4. Procesar operaciones de la cuenta
                foreach ($cuentaData['operaciones'] as $operacionData) {
                    if (empty($operacionData['Date']) || empty($operacionData['Opérations'])) {
                        Log::warning('Operación sin datos básicos, saltando');
                        continue;
                    }

                    $hash = $this->generarHashOperacion($operacionData, $cuenta->id);

                    // La fecha ahora ya incluye año: "29/12/2016"
                    $fechaConvertida = $this->convertirFecha($operacionData['Date'], $extract['anio_documento'] ?? 2016);

                    CuentaOperaciones::updateOrCreate(
                        ['hash_operacion' => $hash],
                        [
                            'cuenta_id' => $cuenta->id,
                            'fecha_operacion' => $fechaConvertida,
                            'descripcion_operacion' => $operacionData['Opérations'],
                            'debito' => $operacionData['Débit'] ?? 0,
                            'credito' => $operacionData['Crédit'] ?? 0,
                            'valor_francos' => $operacionData['francs'] ?? 0,
                            'hash_operacion' => $hash,
                        ],
                    );

                    Log::info("Operación guardada/actualizada: {$operacionData['Date']} - {$operacionData['Opérations']}");
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

            if (file_exists($tempOut)) {
                unlink($tempOut);
            }

            Log::error("Error procesando PDF {$item['name']}: " . $e->getMessage());
        }
    }

    $this->isProcessing = false;
    $this->status = "Proceso terminado. Correctos: {$this->stats['procesados']}, Errores: {$this->stats['errores']}";
};
?>

<!-- El HTML se mantiene igual que antes -->
<div class="bg-white dark:bg-gray-900 p-6 border border-gray-100 shadow-sm rounded-lg">

  {{-- MONITOR DE ESTADO --}}
  <div class="mb-8 flex justify-between items-start">
    <div>
      <span class="text-[10px] font-black uppercase text-indigo-600 italic tracking-[0.2em]">Sincronizador
        Bancario</span>
      <div class="mt-2 p-3 bg-gray-50 border border-dashed border-gray-200 min-w-[400px] rounded-md">
        <p class="text-[11px] font-mono text-gray-500">>>> {{ $status }}</p>
      </div>
    </div>

    <div class="flex flex-col gap-2">
      @php
        $canProcess = collect($fileQueue)->where('status', '!=', 'success')->count() > 0;
      @endphp

      <button wire:click="processPdf" wire:loading.attr="disabled" @if (!$canProcess || $isProcessing) disabled @endif
        class="px-4 py-2 text-sm font-medium uppercase tracking-wider transition-all rounded-md
                    {{ $canProcess && !$isProcessing ? 'bg-black text-white hover:bg-indigo-700 shadow-md' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">

        <div class="flex items-center justify-center gap-2">
          <svg wire:loading wire:target="processPdf" class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <span>
            {{ $isProcessing ? 'Procesando...' : ($canProcess ? 'Procesar Archivos' : 'Sin archivos') }}
          </span>
        </div>
      </button>

      @if (count($fileQueue) > 0)
        <button wire:click="clearQueue" wire:loading.attr="disabled" @if ($isProcessing) disabled @endif
          class="text-sm font-medium text-red-500 hover:text-red-700 hover:underline uppercase">
          Limpiar todo
        </button>
      @endif
    </div>
  </div>

  {{-- ÁREA DE SUBIDA DE ARCHIVOS --}}
  <div class="mb-8">
    <div class="space-y-4">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
        {{ __('Seleccionar PDFs Bancarios') }}
      </label>

      <div
        class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md dark:border-gray-600 hover:border-indigo-400 transition-colors relative">
        <div class="space-y-1 text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"
            aria-hidden="true">
            <path
              d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
          </svg>

          {{-- Estado de subida --}}
          @if ($isUploading)
            <div class="space-y-2">
              <p class="text-sm text-gray-600 dark:text-gray-400">
                Subiendo archivos...
              </p>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="h-full bg-indigo-600 transition-all duration-500 rounded-full"
                  style="width: {{ $uploadProgress }}%"></div>
              </div>
              <p class="text-xs text-gray-500">{{ $uploadProgress }}% completado</p>
            </div>
          @else
            <div class="flex text-sm text-gray-600 dark:text-gray-400">
              <label for="pdf-files"
                class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                <span>{{ __('Subir archivos') }}</span>
                <input id="pdf-files" name="pdf-files[]" type="file" class="sr-only" accept=".pdf" multiple
                  wire:model="archivosSeleccionados" wire:loading.attr="disabled" wire:target="archivosSeleccionados">
              </label>
              <p class="pl-1">{{ __('o arrastra y suelta') }}</p>
            </div>
          @endif

          <p class="text-xs text-gray-500 dark:text-gray-400">
            {{ __('PDFs hasta 10MB cada uno') }}
          </p>
        </div>

        <!-- Indicador de carga de Livewire -->
        <div wire:loading wire:target="archivosSeleccionados"
          class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg">
          <div class="text-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
              </circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <p class="mt-2 text-sm text-gray-600">Subiendo archivos...</p>
          </div>
        </div>
      </div>

      @error('archivosSeleccionados.*')
        <p class="text-sm text-red-600">{{ $message }}</p>
      @enderror
    </div>

    <!-- Información adicional para PDFs -->
    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
      <div class="flex items-start">
        <svg class="h-5 w-5 text-blue-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div class="text-sm text-blue-700">
          <p class="font-medium">Información importante:</p>
          <p class="mt-1">Solo se aceptan archivos PDF de extractos bancarios. Asegúrate de que los PDFs no estén
            protegidos por contraseña.</p>
          <p class="mt-1 text-xs">Soporte para: La Banque Postale, y otros bancos configurados.</p>
        </div>
      </div>
    </div>
  </div>

  {{-- LISTA DE ARCHIVOS EN COLA --}}
  @if (count($fileQueue) > 0)
    <div class="mt-8">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">
          Archivos en cola de procesamiento
          <span class="text-sm font-normal text-gray-500 ml-2">
            ({{ count($fileQueue) }} archivo{{ count($fileQueue) !== 1 ? 's' : '' }})
          </span>
        </h3>

        @if (count($fileQueue) > 0)
          <div class="text-sm text-gray-500">
            <span class="font-medium text-green-600">{{ $stats['procesados'] }}</span> procesados •
            <span class="font-medium text-red-600">{{ $stats['errores'] }}</span> errores
          </div>
        @endif
      </div>

      <div class="space-y-3">
        @foreach ($fileQueue as $index => $file)
          <div
            class="p-4 border {{ $file['status'] === 'error' ? 'border-red-200 bg-red-50' : ($file['status'] === 'success' ? 'border-green-200 bg-green-50' : 'border-gray-200 bg-white') }} rounded-lg shadow-sm transition-all">
            <div class="flex justify-between items-center mb-3">
              <div class="flex items-center gap-3">
                <span class="text-xs font-mono text-gray-400 bg-gray-100 px-2 py-1 rounded">#{{ $index + 1 }}</span>
                <div>
                  <span class="text-sm font-medium text-gray-800">{{ $file['name'] }}</span>
                  <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-500">{{ $file['size'] }}</span>
                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">PDF</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <span
                  class="text-xs font-medium uppercase px-2 py-1 rounded-full {{ $file['status'] === 'success' ? 'bg-green-100 text-green-800' : ($file['status'] === 'error' ? 'bg-red-100 text-red-800' : ($file['status'] === 'loading' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                  {{ $file['status'] === 'loading' ? 'procesando' : ($file['status'] === 'pending' ? 'pendiente' : $file['status']) }}
                </span>

                @if ($file['status'] === 'error')
                  <button type="button" @click="$wire.fileQueue[{{ $index }}].error = null"
                    class="text-xs text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                @endif
              </div>
            </div>

            {{-- Barra de progreso --}}
            @if ($file['status'] === 'loading')
              <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                <div class="h-full bg-indigo-600 transition-all duration-500"
                  style="width: {{ $file['progress'] }}%"></div>
              </div>
              <div class="mt-1 text-xs text-gray-500 text-right">
                {{ $file['progress'] }}%
              </div>
            @elseif($file['status'] === 'success')
              <div class="flex items-center text-green-600 text-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>Procesado correctamente</span>
              </div>
            @endif

            @if ($file['error'])
              <div class="mt-2 p-2 bg-red-50 border border-red-100 rounded">
                <div class="flex items-start">
                  <svg class="h-4 w-4 text-red-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <p class="text-xs text-red-700">{{ $file['error'] }}</p>
                </div>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @else
    {{-- Estado vacío --}}
    <div class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
      </svg>
      <h3 class="mt-4 text-lg font-medium text-gray-900">Sin archivos seleccionados</h3>
      <p class="mt-2 text-sm text-gray-500">
        Selecciona uno o más archivos PDF de extractos bancarios para comenzar el procesamiento.
      </p>
    </div>
  @endif
</div>
