<?php

use App\Http\Controllers\AcademicPerformanceController;
use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SectionStudentController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AiController;

Route::redirect('/', 'login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    //Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // PROGRAMAS ACADÉMICOS
    Route::resource('academic-programs', AcademicProgramController::class);

    // CURSOS
    Route::resource('courses', CourseController::class);

    // SECCIONES
    Route::resource('sections', SectionController::class);

    // Gestión de estudiantes en secciones
    Route::post('sections/{section}/students', [SectionStudentController::class, 'store'])
        ->name('sections.students.store');
    Route::delete('sections/{section}/students/{student}', [SectionStudentController::class, 'destroy'])
        ->name('sections.students.destroy');

    // MATERIALES
    Route::resource('materials', MaterialController::class);
    Route::get('materials/{material}/download', [MaterialController::class, 'download'])
        ->name('materials.download');

    // SOLICITUDES DE MATERIALES
    Route::get('material-requests/pending', [MaterialRequestController::class, 'pending'])
        ->name('material-requests.pending');
    Route::post('material-requests/create', [MaterialRequestController::class, 'store'])
        ->name('material-requests.store');
    Route::post('material-requests/{materialRequest}/approve', [MaterialRequestController::class, 'approve'])
        ->name('material-requests.approve');
    Route::post('material-requests/{materialRequest}/reject', [MaterialRequestController::class, 'reject'])
        ->name('material-requests.reject');

    // RENDIMIENTO ACADÉMICO
    Route::get('academic-performance', [AcademicPerformanceController::class, 'index'])
        ->name('academic-performance.index');

    // ACTIVIDADES
    Route::resource('sections.activities', ActivityController::class)
        ->except(['index'])
        ->shallow();

    Route::get('sections/{section}/activities', [ActivityController::class, 'index'])
        ->name('sections.activities.index');

    // CALIFICACIONES
    Route::post('activities/{activity}/grades', [GradeController::class, 'store'])
        ->name('activities.grades.store');

    // RECOMENDACIONES IA - Sistema completo
    Route::get('/recommendations', [AiController::class, 'index'])->name('recommendations.index');
    Route::post('/ai/recommend', [AiController::class, 'recommend'])->name('ai.recommend');
    Route::get('/ai/recommendations/history/{student}', [AiController::class, 'history'])->name('ai.history');
    Route::patch('/ai/recommendations/{recommendation}/status', [AiController::class, 'updateStatus'])->name('ai.update-status');
    Route::get('/ai/recommendations/export/{student}', [AiController::class, 'export'])->name('ai.export');
    Route::get('/api/ai/statistics', [AiController::class, 'statistics'])->name('ai.statistics');

});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
