<?php

use App\Http\Controllers\AcademicPerformanceController;
use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SectionStudentController;
use App\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/', 'login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    //Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render(component: 'Dashboard');
    })->name('dashboard');

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

    // RECOMENDACIONES (el servicio de python futuramente se integrará aquí)
    // Route::resource('recommendations', RecommendationController::class);

});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
