<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('professor_id')->constrained('users')->onDelete('restrict');
            $table->string('name'); // Ej: "Sección A", "Grupo 1"
            $table->string('academic_period'); // Ej: "2025-1", "2025-2"
            $table->json('schedule')->nullable(); // Días y horarios
            $table->integer('max_students')->default(30);
            $table->enum('status', ['open', 'closed', 'completed'])->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
