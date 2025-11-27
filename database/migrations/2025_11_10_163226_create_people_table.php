<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('card_id')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('enrollment_number')->unique()->nullable()->comment('Matrícula: para estudiantes YYYY-#### (ej: 2025-0001), para profesores NombreApellido-DEPT-## (ej: JuanPerez-ISW-01)');
            $table->date('date_of_birth')->nullable()->comment('Fecha de nacimiento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
