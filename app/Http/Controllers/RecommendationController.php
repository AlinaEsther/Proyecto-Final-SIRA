<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecommendationController extends Controller
{
    /**
     * Display a listing of recommendations.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $recommendations = Recommendation::with(['professor.person', 'student.person'])
            ->when($user->isProfessor(), function($q) use ($user) {
                // Profesores ven las recomendaciones que han hecho
                $q->where('professor_id', $user->id);
            })
            ->when($user->isStudent(), function($q) use ($user) {
                // Estudiantes ven sus propias recomendaciones
                $q->where('student_id', $user->id);
            })
            ->latest()
            ->paginate(15);

        return Inertia::render('Recommendations/Index', [
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Show the form for creating a new recommendation.
     */
    public function create(Request $request)
    {
        // Solo profesores pueden crear recomendaciones
        if (!$request->user()->isProfessor()) {
            abort(403, 'Solo los profesores pueden crear recomendaciones.');
        }

        return Inertia::render('Recommendations/Create');
    }

    /**
     * Store a newly created recommendation.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        // Solo profesores pueden crear recomendaciones
        if (!$user->isProfessor()) {
            abort(403, 'Solo los profesores pueden crear recomendaciones.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'type' => 'required|in:academic,behavioral,general',
        ]);

        $validated['professor_id'] = $user->id;
        $recommendation = Recommendation::create($validated);

        return redirect()->route('recommendations.show', $recommendation)
            ->with('success', 'Recomendación creada exitosamente.');
    }

    /**
     * Display the specified recommendation.
     */
    public function show(Request $request, Recommendation $recommendation)
    {
        $user = $request->user();

        // Verificar acceso
        if ($user->isProfessor() && $recommendation->professor_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta recomendación.');
        }

        if ($user->isStudent() && $recommendation->student_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta recomendación.');
        }

        $recommendation->load(['professor.person', 'student.person']);

        return Inertia::render('Recommendations/Show', [
            'recommendation' => $recommendation,
        ]);
    }

    /**
     * Show the form for editing the recommendation.
     */
    public function edit(Request $request, Recommendation $recommendation)
    {
        $user = $request->user();

        // Solo el profesor que creó la recomendación puede editarla
        if ($recommendation->professor_id !== $user->id) {
            abort(403, 'Solo puedes editar tus propias recomendaciones.');
        }

        $recommendation->load(['student.person']);

        return Inertia::render('Recommendations/Edit', [
            'recommendation' => $recommendation,
        ]);
    }

    /**
     * Update the specified recommendation.
     */
    public function update(Request $request, Recommendation $recommendation)
    {
        $user = $request->user();

        // Solo el profesor que creó la recomendación puede actualizarla
        if ($recommendation->professor_id !== $user->id) {
            abort(403, 'Solo puedes actualizar tus propias recomendaciones.');
        }

        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'type' => 'required|in:academic,behavioral,general',
        ]);

        $recommendation->update($validated);

        return redirect()->route('recommendations.show', $recommendation)
            ->with('success', 'Recomendación actualizada exitosamente.');
    }

    /**
     * Remove the specified recommendation.
     */
    public function destroy(Request $request, Recommendation $recommendation)
    {
        $user = $request->user();

        // Solo el profesor que creó la recomendación puede eliminarla
        if ($recommendation->professor_id !== $user->id) {
            abort(403, 'Solo puedes eliminar tus propias recomendaciones.');
        }

        $recommendation->delete();

        return redirect()->route('recommendations.index')
            ->with('success', 'Recomendación eliminada exitosamente.');
    }
}
