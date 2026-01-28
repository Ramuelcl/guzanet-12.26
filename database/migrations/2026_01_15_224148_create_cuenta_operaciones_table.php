<?php
// database/migrations/2026_01_15_224148_create_cuenta_operaciones_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $table = 'cuenta_operaciones';

    public function up(): void {
        Schema::create($this->table, function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_id')->constrained()->onDelete('cascade');
            $table->date('fecha_operacion');
            $table->text('descripcion_operacion');
            $table->decimal('debito', 15, 2)->default(0);
            $table->decimal('credito', 15, 2)->default(0);
            $table->decimal('valor_francos', 15, 2)->nullable();
            $table->string('hash_operacion')->unique();
            $table->timestamps();

            $table->index(['cuenta_id', 'fecha_operacion']);
            $table->index('hash_operacion');
        });
    }

    public function down(): void {
        Schema::dropIfExists($this->table);
    }
};
