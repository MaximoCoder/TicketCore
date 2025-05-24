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
        Schema::table('ticket_categories', function (Blueprint $table) {
            // Eliminamos la foreign key
            $table->dropForeign('ticket_categories_department_id_foreign');
            // Eliminamos department_id
            $table->dropColumn('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_categories', function (Blueprint $table) {
            // Añadimos department_id
            $table->foreignId('department_id')->after('is_active')->constrained()->nullOnDelete();
        });
    }
};
