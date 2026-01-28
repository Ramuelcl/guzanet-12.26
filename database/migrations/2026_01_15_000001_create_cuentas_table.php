<?php
// database/migrations/2026_01_15_000001_create_cuentas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public $table = 'cuentas';

  public function up(): void {
    Schema::create($this->table, function (Blueprint $table) {
      $table->id();
      $table->string('numero_cuenta')->unique();
      $table->decimal('saldo_actual', 15, 2)->default(0);
      $table->decimal('saldo_anterior', 15, 2)->default(0);
      $table->string('tipo')->default(null);
      $table->string('iban')->nullable()->default(null);
      $table->string('bic')->nullable()->default(null);

      // Relaciones con Entidad
      $table->foreignId('banco_entidad_id')->nullable()->constrained('entidades')->onDelete('set null');
      $table->foreignId('cliente_entidad_id')->nullable()->constrained('entidades')->onDelete('set null');

      // Información básica (opcional, ya que estará en Entidad)
      $table->string('banco_nombre')->nullable()->default(null);
      $table->string('cliente_nombre')->nullable()->default(null);
      $table->string('cliente_id')->nullable()->default(null);

      $table->timestamps();

      // Índices
      $table->index(['banco_entidad_id', 'cliente_entidad_id']);
    });
  }

  public function down(): void {
    Schema::dropIfExists($this->table);
  }
};
