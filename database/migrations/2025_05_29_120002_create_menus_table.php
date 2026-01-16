<?php
// database/migrations/2025_05_29_120002_create_menus_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private string $table = 'menus';
    private string $table1 = 'menu_id_counters';
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create($this->table, function (Blueprint $table) {
            // $table->id(); // Esto crea un BIGINT auto-incrementable y primario
            $table->unsignedBigInteger('id')->primary(); // Define 'id' como BIGINT unsigned y clave primaria, pero NO auto-incrementable
            $table->string('name', 16)->notNullable();
            $table->string('url', 128)->nullable();
            $table->string('icon', 16)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0)->comment('Order of the menu item, lower numbers appear first')->index('order_index');
            $table->string('type', 16)->default('link')->comment('Type of menu item: link, dropdown, etc.')->index('type_index');
            $table->boolean('is_active')->default(true);

            $table->foreign('parent_id')->references('id')->on($this->table)->onDelete('cascade');
            $table->timestamps();
        });
        Schema::create($this->table1, function (Blueprint $table) {
            $table->string('menu_type', 16)->primary(); // 'sys', 'menu', 'user', etc.
            $table->unsignedBigInteger('next_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists($this->table);
        Schema::dropIfExists($this->table1);
    }
};
