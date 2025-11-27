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
        Schema::table('section_student', function (Blueprint $table) {
            // Cambiar el enum de status para incluir enrollment states
            $table->enum('status', ['pending', 'enrolled', 'dropped', 'completed', 'rejected'])->default('pending')->change();

            // Agregar campos de aprobación de enrollment
            $table->foreignId('enrolled_by')->nullable()->after('status')->constrained('users')->onDelete('set null'); // Admin que aprobó
            $table->timestamp('enrolled_at')->nullable()->after('enrolled_by'); // Cuándo fue aprobado
            $table->text('enrollment_notes')->nullable()->after('enrolled_at'); // Notas del admin
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('section_student', function (Blueprint $table) {
            $table->dropForeign(['enrolled_by']);
            $table->dropColumn(['enrolled_by', 'enrolled_at', 'enrollment_notes']);

            // Revertir el enum a su estado original
            $table->enum('status', ['enrolled', 'dropped', 'completed'])->default('enrolled')->change();
        });
    }
};
