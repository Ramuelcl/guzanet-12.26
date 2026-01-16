<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public $table = 'marcadores';
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::disableForeignKeyConstraints();

    Schema::create($this->table, function (Blueprint $table) {
      $table->id();
      $table->string('nombre', 45)->nullable()->default(null)->unique()->charset('utf8');
      $table->string('slug', 45)->nullable()->default(null)->charset('utf8');
      $table->string('hexa', 7)->unique()->default('#')->charset('utf8');
      $table->string('rgb', 20)->nullable()->default(null)->charset('utf8');
      $table->json('metadata')->nullable();
      $table->boolean('is_active')->default(0);
      $table->timestamps();
      $table->softDeletes();
    });

    Schema::enableForeignKeyConstraints();
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists($this->table);
  }
};
