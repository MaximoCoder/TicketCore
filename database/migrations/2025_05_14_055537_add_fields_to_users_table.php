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
        Schema::table('users', function (Blueprint $table) {
            // Añadimos el departament_id para relacionar al user con un departamento
            $table->foreignId('department_id')->nullable()->after('is_active')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Borramos el departamento_id
            $table->dropColumn('department_id');
            $table->dropForeign('users_department_id_foreign');
        });
    }
};
