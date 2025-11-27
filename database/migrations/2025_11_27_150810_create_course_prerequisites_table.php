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
        Schema::create('course_prerequisites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade'); // Curso que tiene prerrequisitos
            $table->foreignId('prerequisite_course_id')->constrained('courses')->onDelete('cascade'); // Curso que es prerrequisito
            $table->boolean('is_mandatory')->default(true); // Si es obligatorio cumplirlo
            $table->timestamps();

            // Un curso no puede tener el mismo prerrequisito dos veces
            $table->unique(['course_id', 'prerequisite_course_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_prerequisites');
    }
};
