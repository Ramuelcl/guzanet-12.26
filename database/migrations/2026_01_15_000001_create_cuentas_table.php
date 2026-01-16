<?php
// C:\laragon\www\laravel\guzanet-12.26\database\migrations\2026_01_15_000001_create_cuentas_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public $table = 'cuentas';

  /**
   * Run the migrations.
   */
  public function up(): void {
    Schema::create($this->table, function (Blueprint $table) {
      $table->id();
      $table->string('banco_nombre', 100)->default('La Banque Postale');
      $table->string('cliente_id', 20);
      $table->string('nombre_cliente', 100);
      $table->string('direccion_cliente', 200);
      $table->date('fecha_reporte');
      $table->string('numero_cuenta', 30);
      $table->string('tipo_cuenta', 50);
      $table->string('iban', 34)->unique();
      $table->string('bic', 11);
      $table->decimal('saldo_anterior', 15, 2);
      $table->decimal('saldo_actual', 15, 2);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void {
    Schema::dropIfExists($this->table);
  }
};