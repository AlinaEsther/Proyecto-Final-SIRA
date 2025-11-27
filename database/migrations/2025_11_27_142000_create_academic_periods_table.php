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
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Ej: "2025-C1", "2025-C2", "2025-S1"
            $table->string('name'); // Ej: "Primer Cuatrimestre 2025"
            $table->enum('type', ['cuatrimestre', 'semestre']); // Tipo de periodo
            $table->integer('year'); // Año: 2025, 2026, etc.
            $table->integer('number'); // Número del periodo: 1, 2, 3, 4
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false); // Solo un periodo puede estar activo
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
