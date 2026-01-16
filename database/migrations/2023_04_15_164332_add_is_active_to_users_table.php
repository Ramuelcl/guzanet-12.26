<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  protected $table = 'users';
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::disableForeignKeyConstraints();

    Schema::table($this->table, function (Blueprint $table) {
      // agregar un campo
      $table->boolean('is_active')->default(true)->after('password');
      $table->string('profile_photo_path', 128)->default(null)->nullable()->after('password');
      // $table->string('role', 20)->default(null)->after('profile_photo_path');
      // modificar campos
      // * para alterar campos hay que ejecutar antes
      // * composer require doctrine/dbal
      // $table->string('name', 50)->nullable()->change();
      // $table->string('password')->nullable()->default(null)->change();
    });

    Schema::enableForeignKeyConstraints();
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::disableForeignKeyConstraints();

    Schema::table($this->table, function (Blueprint $table) {
      $table->dropColumn('is_active');
      // $table->string('name', 255)->nullable(false)->change();
      // $table->string('password', 255)->change();
    });

    Schema::enableForeignKeyConstraints();
  }
};
