<?php
// database/migrations/2026_01_24_000001_create_tickets_table.php

  use Illuminate\Database\Migrations\Migration;
  use Illuminate\Database\Schema\Blueprint;
  use Illuminate\Support\Facades\Schema;

  return new class extends Migration {
    public function up(): void {
      Schema::create('tickets', function (Blueprint $table) {
        $table->id();
        // Referencia al cliente (si usas una tabla separada de users)
        $table->foreignId('customer_id')->nullable()->constrained('users')->onDelete('set null');
        // Referencia al agente/staff que atiende
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        // Identificador para invitados
        $table->string('session_id')->nullable()->index();
        $table->string('subject');
        $table->enum('status', ['open', 'pending', 'closed'])->default('open');
        $table->timestamps();
      });

      Schema::create('ticket_messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
        $table->string('session_id')->nullable();
        $table->text('body');
        $table->timestamps();
      });
    }

    public function down(): void {
      Schema::dropIfExists('ticket_messages');
      Schema::dropIfExists('tickets');
    }
  };
