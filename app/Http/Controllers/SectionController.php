<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SectionController extends Controller
{
    /**
     * Display a listing of sections.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $sections = Section::with(['course', 'professor.person'])
            ->when($user->isProfessor(), fn($q) => $q->where('professor_id', $user->id))
            ->when($user->isStudent(), fn($q) => $q->whereHas('students', fn($sq) => $sq->where('student_id', $user->id)))
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->when($request->academic_period, fn($q) => $q->where('academic_period', $request->academic_period))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15);

        return Inertia::render('Sections/Index', [
            'sections' => $sections,
            'filters' => $request->only(['course_id', 'academic_period', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new section.
     */
    public function create()
    {
        $courses = Course::active()->with('academicProgram')->get();
        $professors = User::professors()->with('person')->get();

        return Inertia::render('Sections/Create', [
            'courses' => $courses,
            'professors' => $professors,
        ]);
    }

    /**
     * Store a newly created section.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'professor_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'academic_period' => 'required|string|max:255',
            'schedule' => 'nullable|array',
            'max_students' => 'required|integer|min:1',
            'status' => 'required|in:open,closed,completed',
        ]);

        $section = Section::create($validated);

        return redirect()->route('sections.show', $section)
            ->with('success', 'Sección creada exitosamente.');
    }

    /**
     * Display the specified section.
     */
    public function show(Request $request, Section $section)
    {
        $user = $request->user();

        // Verificar que el usuario tenga acceso a esta sección
        if ($user->isProfessor() && $section->professor_id !== $user->id) {
            abort(403, 'No tienes permiso para ver esta sección.');
        }

        if ($user->isStudent() && !$section->students()->where('student_id', $user->id)->exists()) {
            abort(403, 'No estás inscrito en esta sección.');
        }

        $section->load([
            'course',
            'professor.person',
            'students.person',
            'materials',
        ]);

        // Contar actividades publicadas
        $activitiesCount = $section->activities()->count();

        return Inertia::render('Sections/Show', [
            'section' => $section,
            'statistics' => [
                'enrolled_count' => $section->students()->count(),
                'average_grade' => $section->students()->avg('section_student.current_grade'),
                'activities_count' => $activitiesCount,
            ],
        ]);
    }

    /**
     * Show the form for editing the section.
     */
    public function edit(Section $section)
    {
        $section->load(['course', 'professor.person']);

        $courses = Course::active()->with('academicProgram')->get();
        $professors = User::professors()->with('person')->get();

        return Inertia::render('Sections/Edit', [
            'section' => $section,
            'courses' => $courses,
            'professors' => $professors,
        ]);
    }

    /**
     * Update the specified section.
     */
    public function update(Request $request, Section $section)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'professor_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'academic_period' => 'required|string|max:255',
            'schedule' => 'nullable|array',
            'max_students' => 'required|integer|min:1',
            'status' => 'required|in:open,closed,completed',
        ]);

        $section->update($validated);

        return redirect()->route('sections.show', $section)
            ->with('success', 'Sección actualizada exitosamente.');
    }

    /**
     * Remove the specified section.
     */
    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()->route('sections.index')
            ->with('success', 'Sección eliminada exitosamente.');
    }
}
