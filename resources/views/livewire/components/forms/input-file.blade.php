<?php
// resources/views/livewire/components/forms/input-file.blade.php

use function Livewire\Volt\{state, mount, uses};
use Livewire\WithFileUploads;

// Habilitamos la subida de archivos
uses([WithFileUploads::class]);

// Propiedades del componente
state([
    'acceptedFiles' => 'image/*,.pdf,.doc,.docx,.xls,.xlsx',
    'maxSize' => 2,
    'maxFiles' => 5,
    'allowMultiple' => true,
    'label' => 'Seleccionar archivos',
    'placeholder' => 'Arrastra archivos aquí o haz clic para seleccionar',
    'showFileList' => true,
    'previewImages' => true,
    'files' => [],
    'selectedFiles' => [],
    'error' => null,
    'isDragging' => false,
]);

// Cuando se actualizan los archivos
$updatedFiles = function($value) {
    if (!$value) return;
    
    $this->error = null;
    
    // Validar número máximo de archivos
    $totalFiles = count($this->selectedFiles) + (is_array($value) ? count($value) : 1);
    if ($totalFiles > $this->maxFiles) {
        $this->error = "Máximo {$this->maxFiles} archivos permitidos";
        $this->files = [];
        return;
    }
    
    // Procesar nuevos archivos
    $uploads = is_array($value) ? $value : [$value];
    
    foreach ($uploads as $file) {
        // Validar tamaño
        $sizeInMB = $file->getSize() / 1024 / 1024;
        if ($sizeInMB > $this->maxSize) {
            $this->error = "El archivo {$file->getClientOriginalName()} excede el tamaño máximo de {$this->maxSize}MB";
            continue;
        }
        
        // Agregar a la lista
        $this->selectedFiles[] = [
            'id' => uniqid(),
            'fileObject' => $file,
            'name' => $file->getClientOriginalName(),
            'size' => $this->formatBytes($file->getSize()),
            'type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'isImage' => str_starts_with($file->getMimeType(), 'image/'),
            'temporaryUrl' => $file->temporaryUrl(),
            'realPath' => $file->getRealPath(),
            'uploadedAt' => now(),
        ];
    }
    
    // Limpiar el input de Livewire
    $this->files = [];
    
    // Emitir evento con los archivos seleccionados
    $this->dispatch('files-selected', files: $this->getFilesData());
};

// Formatear tamaño en bytes
$formatBytes = function($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
};

// Eliminar archivo
$removeFile = function($index) {
    if (isset($this->selectedFiles[$index])) {
        array_splice($this->selectedFiles, $index, 1);
        $this->dispatch('files-updated', files: $this->getFilesData());
    }
};

// Eliminar todos los archivos
$clearAll = function() {
    $this->selectedFiles = [];
    $this->files = [];
    $this->error = null;
    $this->dispatch('files-cleared');
};

// Obtener datos de archivos para retornar
$getFilesData = function() {
    return array_map(function($file) {
        return [
            'name' => $file['name'],
            'size' => $file['size'],
            'type' => $file['type'],
            'extension' => $file['extension'],
            'temporary_path' => $file['realPath'],
            'original_name' => $file['fileObject']->getClientOriginalName(),
            'mime_type' => $file['fileObject']->getMimeType(),
            'size_bytes' => $file['fileObject']->getSize(),
            'is_image' => $file['isImage'],
        ];
    }, $this->selectedFiles);
};

// Manejar eventos de arrastre
$handleDragEnter = function() {
    $this->isDragging = true;
};

$handleDragLeave = function() {
    $this->isDragging = false;
};

$handleDrop = function() {
    $this->isDragging = false;
};
?>

<div x-data="{ openViewer: false, currentFile: null }" class="input-file-component">
    <!-- Zona de arrastre y selección -->
    <div class="mb-4"
         @dragenter.prevent="handleDragEnter"
         @dragover.prevent
         @dragleave.prevent="handleDragLeave"
         @drop.prevent="handleDrop">
        
        <div class="relative border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200
                    {{ $isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-gray-400' }}
                    {{ $error ? 'border-red-300 bg-red-50' : '' }}">
            
            <!-- Input de archivo -->
            <input type="file" 
                   accept="{{ $acceptedFiles }}"
                   @if($allowMultiple) multiple @endif
                   wire:model="files"
                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
            
            <!-- Contenido visual -->
            <div class="relative z-0">
                <div class="flex flex-col items-center justify-center space-y-3">
                    <!-- Icono -->
                    <div class="p-3 rounded-full bg-gray-100">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    
                    <!-- Textos -->
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $placeholder }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Tipos permitidos: {{ $acceptedFiles }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Tamaño máximo: {{ $maxSize }}MB por archivo
                            @if($maxFiles > 1)
                                • Máximo {{ $maxFiles }} archivos
                            @endif
                        </p>
                    </div>
                    
                    <!-- Botón de selección -->
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        {{ $label }}
                    </button>
                </div>
                
                <!-- Indicador de carga -->
                <div wire:loading wire:target="files" class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-lg">
                    <div class="text-center">
                        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Subiendo archivos...</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mensaje de error -->
        @if($error)
            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm text-red-700">{{ $error }}</span>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Lista de archivos seleccionados -->
    @if($showFileList && count($selectedFiles) > 0)
        <div class="mt-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-lg font-medium text-gray-900">
                    Archivos seleccionados ({{ count($selectedFiles) }})
                </h3>
                @if(count($selectedFiles) > 0)
                    <button type="button" 
                            wire:click="clearAll"
                            class="text-sm text-red-600 hover:text-red-800 font-medium">
                        Eliminar todos
                    </button>
                @endif
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($selectedFiles as $index => $file)
                    <div class="relative group border border-gray-200 rounded-lg p-4 hover:border-gray-300 transition-all">
                        <!-- Botón eliminar -->
                        <button type="button"
                                wire:click="removeFile({{ $index }})"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity z-20 hover:bg-red-600">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                        
                        <!-- Contenido del archivo -->
                        <div class="flex items-start space-x-3">
                            <!-- Preview/Icono -->
                            <div class="flex-shrink-0">
                                @if($file['isImage'])
                                    <img src="{{ $file['temporaryUrl'] }}" 
                                         alt="{{ $file['name'] }}"
                                         class="w-16 h-16 object-cover rounded-lg cursor-pointer"
                                         @click="openViewer = true; currentFile = '{{ $file['temporaryUrl'] }}'">
                                @else
                                    <div class="w-16 h-16 flex items-center justify-center bg-gray-100 rounded-lg">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Información del archivo -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" title="{{ $file['name'] }}">
                                    {{ $file['name'] }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $file['size'] }} • {{ strtoupper($file['extension']) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $file['uploadedAt']->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>