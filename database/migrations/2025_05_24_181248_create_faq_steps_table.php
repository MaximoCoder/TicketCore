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
        Schema::create('faq_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained()->onDelete('cascade'); // Relación con la FAQ
            $table->string('title')->nullable(); // Título del paso (opcional)
            $table->text('content'); // Texto del paso
            $table->string('image_path')->nullable(); // Ruta de imagen si se sube una
            $table->integer('step_order')->default(0); // Orden del paso
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faq_steps');
    }
};
