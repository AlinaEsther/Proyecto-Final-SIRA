<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Section;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request, Section $section)
    {
        $user = $request->user();

        // Verificar acceso a la sección
        if ($user->isProfessor() && $section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'No tienes permiso para ver las actividades de esta sección.'
            ]);
        }

        if ($user->isStudent() && !$section->students()->where('student_id', $user->id)->exists()) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'No estás inscrito en esta sección.'
            ]);
        }

        $activities = $section->activities()->with('grades')->latest()->paginate(15);

        return Inertia::render('Activities/Index', [
            'section' => $section,
            'activities' => $activities,
        ]);
    }

    public function create(Request $request, Section $section)
    {
        $user = $request->user();

        // Solo el profesor de la sección puede crear actividades
        if ($section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'Solo el profesor de esta sección puede crear actividades.'
            ]);
        }

        return Inertia::render('Activities/Create', [
            'section' => $section,
        ]);
    }

    public function store(Request $request, Section $section)
    {
        $user = $request->user();

        // Solo el profesor de la sección puede crear actividades
        if ($section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'Solo el profesor de esta sección puede crear actividades.'
            ]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:assignment,practice,project,exam',
            'period' => 'required|string|max:20', // Dinámico: 2025-C1, 2025-S1, etc.
            'max_points' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ]);

        $validated['section_id'] = $section->id;
        $activity = Activity::create($validated);

        return redirect()->route('sections.activities.show', [$section, $activity])
            ->with('alert', [
                'type' => 'success',
                'message' => 'Actividad creada exitosamente.'
            ]);
    }

    public function show(Request $request, Section $section, Activity $activity)
    {
        $user = $request->user();

        // Verificar acceso a la sección
        if ($user->isProfessor() && $section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'No tienes permiso para ver esta actividad.'
            ]);
        }

        if ($user->isStudent() && !$section->students()->where('student_id', $user->id)->exists()) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'No estás inscrito en esta sección.'
            ]);
        }

        $activity->load(['section', 'grades.student.person']);

        return Inertia::render('Activities/Show', [
            'section' => $section,
            'activity' => $activity,
        ]);
    }

    public function edit(Request $request, Section $section, Activity $activity)
    {
        $user = $request->user();

        // Solo el profesor de la sección puede editar actividades
        if ($section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'Solo el profesor de esta sección puede editar actividades.'
            ]);
        }

        return Inertia::render('Activities/Edit', [
            'section' => $section,
            'activity' => $activity,
        ]);
    }

    public function update(Request $request, Section $section, Activity $activity)
    {
        $user = $request->user();

        // Solo el profesor de la sección puede actualizar actividades
        if ($section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'Solo el profesor de esta sección puede actualizar actividades.'
            ]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:assignment,practice,project,exam',
            'period' => 'required|string|max:20', // Dinámico: 2025-C1, 2025-S1, etc.
            'max_points' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ]);

        $activity->update($validated);

        return redirect()->route('sections.activities.show', [$section, $activity])
            ->with('alert', [
                'type' => 'success',
                'message' => 'Actividad actualizada exitosamente.'
            ]);
    }

    public function destroy(Request $request, Section $section, Activity $activity)
    {
        $user = $request->user();

        // Solo el profesor de la sección puede eliminar actividades
        if ($section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'Solo el profesor de esta sección puede eliminar actividades.'
            ]);
        }

        $activity->delete();

        return redirect()->route('sections.activities.index', $section)
            ->with('alert', [
                'type' => 'success',
                'message' => 'Actividad eliminada exitosamente.'
            ]);
    }
}
