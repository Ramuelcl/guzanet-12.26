<?php
// C:\laragon\www\laravel\guzanet-12.26\database\migrations\2026_01_15_000002_create_operaciones_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $table = 'operaciones';

    public function up(): void {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained('cuentas')->onDelete('cascade');
            $table->date('fecha_operacion');
            $table->text('descripcion_operacion');
            $table->decimal('debito', 15, 2)->default(0.00);
            $table->decimal('credito', 15, 2)->default(0.00);
            // El hash es vital para que al subir el PDF de Rafael de nuevo no se dupliquen movimientos
            $table->string('hash_operacion')->unique();
            $table->decimal('valor_francos', 15, 2)->default(0.00);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists($this->table);
    }
};
