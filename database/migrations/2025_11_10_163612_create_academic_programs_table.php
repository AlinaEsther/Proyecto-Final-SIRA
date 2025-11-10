<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Ingeniería de Software"
            $table->string('code')->unique(); // Ej: "ISW"
            $table->text('description')->nullable();
            $table->integer('total_credits'); // Total de créditos para completar el pensum
            $table->integer('total_semesters'); // Cantidad de semestres del programa
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_programs');
    }
};
