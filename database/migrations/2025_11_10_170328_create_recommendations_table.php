<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('section_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('course_name')->nullable();
            $table->text('book_title')->nullable();
            $table->string('book_author')->nullable();
            $table->integer('relevance_score')->default(0);
            $table->decimal('average_grade', 5, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('professor_notes')->nullable();
            $table->json('activities_data')->nullable();
            $table->enum('status', ['pending', 'viewed', 'completed', 'dismissed'])->default('pending');
            $table->timestamps();

            $table->index('student_id');
            $table->index('section_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};

