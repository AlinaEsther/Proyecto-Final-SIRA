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

            // Calificaciones por periodo (calculadas promediando actividades)
            $table->decimal('grade_p1', 5, 2)->nullable();
            $table->decimal('grade_p2', 5, 2)->nullable();
            $table->decimal('grade_p3', 5, 2)->nullable();
            $table->decimal('grade_exam', 5, 2)->nullable();
            $table->decimal('current_grade', 5, 2)->nullable(); // Nota actual acumulada
            $table->decimal('final_grade', 5, 2)->nullable(); // Nota final
            $table->char('letter_grade', 1)->nullable(); // Grado literal (A, B, C, F)

            $table->timestamps();

            $table->unique(['section_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_student');
    }
};
