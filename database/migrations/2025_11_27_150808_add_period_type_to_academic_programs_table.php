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
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->enum('period_type', ['cuatrimestre', 'semestre'])->default('cuatrimestre')->after('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_programs', function (Blueprint $table) {
            $table->dropColumn('period_type');
        });
    }
};
