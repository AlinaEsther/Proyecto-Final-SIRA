<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    /**
     * Display a listing of grades.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $grades = Grade::with(['activity.section', 'student.person'])
            ->when($user->isProfessor(), function($q) use ($user) {
                // Profesores ven calificaciones de sus secciones
                $q->whereHas('activity.section', fn($sq) => $sq->where('professor_id', $user->id));
            })
            ->when($user->isStudent(), function($q) use ($user) {
                // Estudiantes solo ven sus propias calificaciones
                $q->where('student_id', $user->id);
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Grades/Index', [
            'grades' => $grades,
        ]);
    }

    /**
     * Store a newly created grade.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'activity_id' => 'required|exists:activities,id',
            'student_id' => 'required|exists:users,id',
            'points' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        // Verificar que el profesor es dueño de la sección
        $activity = Activity::findOrFail($validated['activity_id']);
        if ($activity->section->professor_id !== $user->id) {
            abort(403, 'No tienes permiso para calificar esta actividad.');
        }

        $grade = Grade::create($validated);

        return back()->with('success', 'Calificación registrada exitosamente.');
    }

    /**
     * Display the specified grade.
     */
    public function show(Request $request, Grade $grade)
    {
        $user = $request->user();

        // Verificar acceso
        if ($user->isProfessor() && $grade->activity->section->professor_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta calificación.');
        }

        if ($user->isStudent() && $grade->student_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta calificación.');
        }

        $grade->load(['activity', 'student.person']);

        return Inertia::render('Grades/Show', [
            'grade' => $grade,
        ]);
    }

    /**
     * Update the specified grade.
     */
    public function update(Request $request, Grade $grade)
    {
        $user = $request->user();

        // Solo el profesor de la sección puede actualizar calificaciones
        if ($grade->activity->section->professor_id !== $user->id) {
            abort(403, 'No tienes permiso para actualizar esta calificación.');
        }

        $validated = $request->validate([
            'points' => 'required|numeric|min:0',
            'feedback' => 'nullable|string',
        ]);

        $grade->update($validated);

        return back()->with('success', 'Calificación actualizada exitosamente.');
    }

    /**
     * Remove the specified grade.
     */
    public function destroy(Request $request, Grade $grade)
    {
        $user = $request->user();

        // Solo el profesor de la sección puede eliminar calificaciones
        if ($grade->activity->section->professor_id !== $user->id) {
            abort(403, 'No tienes permiso para eliminar esta calificación.');
        }

        $grade->delete();

        return back()->with('success', 'Calificación eliminada exitosamente.');
    }
}
