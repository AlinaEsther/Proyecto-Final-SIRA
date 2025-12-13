<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['assignment', 'practice', 'project', 'exam']); // Tipos: asignaciones teóricas, prácticas, proyectos, exámenes
            $table->string('period'); // Periodo de evaluación: P1, P2, P3, FIN
            $table->decimal('max_points', 5, 2)->default(100);
            $table->datetime('due_date')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
