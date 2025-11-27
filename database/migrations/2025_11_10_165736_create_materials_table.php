<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['video', 'pdf', 'link', 'document']);
            $table->string('file_path')->nullable(); // Para archivos subidos
            $table->string('original_filename')->nullable(); // Nombre original del archivo
            $table->string('url')->nullable(); // Para links externos o videos
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
