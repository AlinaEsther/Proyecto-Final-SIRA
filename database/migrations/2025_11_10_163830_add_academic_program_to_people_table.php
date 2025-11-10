<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // Para estudiantes
            $table->foreignId('academic_program_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null')
                ->after('profile_picture');
            $table->integer('current_semester')->nullable()->after('academic_program_id');
            $table->date('enrollment_date')->nullable()->after('current_semester'); // Ingreso al programa

            // Para profesores
            $table->string('department')->nullable()->after('enrollment_date');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            $table->dropForeign(['academic_program_id']);
            $table->dropColumn(['academic_program_id', 'current_semester', 'enrollment_date', 'department']);
        });
    }
};
