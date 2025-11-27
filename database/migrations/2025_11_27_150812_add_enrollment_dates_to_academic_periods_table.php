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
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->date('enrollment_start_date')->nullable()->after('end_date'); // Inicio de inscripción
            $table->date('enrollment_end_date')->nullable()->after('enrollment_start_date'); // Fin de inscripción
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->dropColumn(['enrollment_start_date', 'enrollment_end_date']);
        });
    }
};
