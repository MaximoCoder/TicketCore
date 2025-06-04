<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('subject');
            $table->text('description');
            $table->foreignId('user_id')->constrained()->comment('Usuario que creó el ticket');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->comment('Usuario de soporte asignado');
            $table->foreignId('department_id')->constrained();
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories');
            $table->foreignId('priority_id')->constrained('ticket_priorities');
            $table->foreignId('status_id')->constrained('ticket_statuses');
            $table->datetime('due_date')->nullable();
            $table->datetime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
