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
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade'); // Quien solicita
            $table->foreignId('recommendation_id')->nullable()->constrained()->onDelete('set null'); // Si viene de una recomendación IA
            $table->string('title'); // Título del material solicitado
            $table->string('author')->nullable(); // Autor del libro/material
            $table->enum('type_requested', ['video', 'pdf', 'link', 'document'])->default('link'); // Tipo esperado
            $table->text('description')->nullable(); // Por qué lo solicita
            $table->string('url')->nullable(); // Si tiene un link sugerido
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null'); // Admin que revisó
            $table->text('admin_notes')->nullable(); // Notas del admin
            $table->foreignId('material_id')->nullable()->constrained()->onDelete('set null'); // Material creado (si fue aprobado)
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index('requested_by');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
