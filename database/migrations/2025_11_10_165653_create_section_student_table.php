<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->date('enrollment_date'); // Fecha de inscripción a la sección
            $table->enum('status', ['enrolled', 'dropped', 'completed'])->default('enrolled');

            // Calificaciones por categoría (calculadas automáticamente desde actividades/grades)
            $table->decimal('assignments_avg', 5, 2)->nullable(); // Promedio de asignaciones
            $table->decimal('grade_p1', 5, 2)->nullable(); // Examen Parcial 1
            $table->decimal('grade_p2', 5, 2)->nullable(); // Examen Parcial 2
            $table->decimal('grade_final', 5, 2)->nullable(); // Examen Final
            $table->decimal('total_grade', 5, 2)->nullable(); // Total (promedio ponderado)
            $table->char('letter_grade', 1)->nullable(); // Grado literal (A, B, C, F)
            $table->integer('absences')->default(0); // Total de ausencias

            $table->timestamps();

            $table->unique(['section_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_student');
    }
};
