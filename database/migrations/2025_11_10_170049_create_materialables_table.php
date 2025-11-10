<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materialables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->onDelete('cascade');
            $table->morphs('materialable'); // materialable_id y materialable_type (Course, Section)
            $table->boolean('is_required')->default(false); // Requerido en esta sección
            $table->integer('order')->default(0); // Orden de presentación
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materialables');
    }
};
