<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Course;
use App\Models\User;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SectionController extends Controller
{
    /**
     * Display a listing of sections.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search', '');

        // NO usar paginate() - la paginación se maneja en frontend con BaseTable
        $sections = Section::with(['course', 'professor.person'])
            ->when($user->isProfessor(), fn($q) => $q->where('professor_id', $user->id))
            ->when($user->isStudent(), fn($q) => $q->whereHas('students', fn($sq) => $sq->where('student_id', $user->id)))
            ->when($request->course_id, fn($q) => $q->where('course_id', $request->course_id))
            ->when($request->academic_period, fn($q) => $q->where('academic_period', $request->academic_period))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('academic_period', 'like', "%{$search}%")
                        ->orWhereHas('course', fn($cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('professor.person', fn($pq) => $pq->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->get();

        return Inertia::render('Sections/Index', [
            'sections' => ['data' => $sections], // Formato compatible con BaseTable
            'filters' => $request->only(['course_id', 'academic_period', 'status', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new section.
     */
    public function create()
    {
        return Inertia::render('Sections/Create', [
            'courses' => Cache::remember('courses', 3600, function () {
                return Course::active()->with('academicProgram')->select('id', 'code', 'name', 'academic_program_id')->get();
            }),
            'professors' => Cache::remember('professors', 3600, function () {
                return User::professors()->with('person')->get()->map(fn($user) => [
                    'id' => $user->id,
                    'person' => [
                        'full_name' => $user->person?->full_name ?? 'Sin nombre'
                    ]
                ]);
            }),
            'materials' => Cache::remember('materials', 3600, function () {
                return Material::select('id', 'title', 'type')->orderBy('title')->get();
            }),
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
            'materials' => 'nullable|array',
            'materials.*.id' => 'required|exists:materials,id',
            'materials.*.is_required' => 'required|boolean',
        ]);

        $section = Section::create($validated);

        // Vincular materiales con pivot is_required si se proporcionaron
        if (!empty($validated['materials'])) {
            $materialsToAttach = [];
            foreach ($validated['materials'] as $material) {
                $materialsToAttach[$material['id']] = ['is_required' => $material['is_required']];
            }
            $section->materials()->attach($materialsToAttach);
        }

        return redirect()->route('sections.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Sección creada exitosamente.'
            ]);
    }

    /**
     * Display the specified section.
     */
    public function show(Request $request, Section $section)
    {
        $user = $request->user();

        // Verificar que el usuario tenga acceso a esta sección
        if ($user->isProfessor() && $section->professor_id !== $user->id) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'No tienes permiso para ver esta sección.'
            ]);
        }

        if ($user->isStudent() && !$section->students()->where('student_id', $user->id)->exists()) {
            return back()->with('alert', [
                'type' => 'danger',
                'message' => 'No estás inscrito en esta sección.'
            ]);
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
        $section->load(['course', 'professor.person', 'materials']);

        return Inertia::render('Sections/Edit', [
            'section' => $section,
            'courses' => Cache::remember('courses', 3600, function () {
                return Course::active()->with('academicProgram')->select('id', 'code', 'name', 'academic_program_id')->get();
            }),
            'professors' => Cache::remember('professors', 3600, function () {
                return User::professors()->with('person')->get()->map(fn($user) => [
                    'id' => $user->id,
                    'person' => [
                        'full_name' => $user->person?->full_name ?? 'Sin nombre'
                    ]
                ]);
            }),
            'materials' => Cache::remember('materials', 3600, function () {
                return Material::select('id', 'title', 'type')->orderBy('title')->get();
            }),
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
            'materials' => 'nullable|array',
            'materials.*.id' => 'required|exists:materials,id',
            'materials.*.is_required' => 'required|boolean',
        ]);

        $section->update($validated);

        // Sincronizar materiales con pivot is_required
        if (isset($validated['materials'])) {
            $materialsToSync = [];
            foreach ($validated['materials'] as $material) {
                $materialsToSync[$material['id']] = ['is_required' => $material['is_required']];
            }
            $section->materials()->sync($materialsToSync);
        } else {
            $section->materials()->detach();
        }

        return redirect()->route('sections.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Sección actualizada exitosamente.'
            ]);
    }

    /**
     * Remove the specified section.
     */
    public function destroy(Section $section)
    {
        $section->delete();

        return redirect()->route('sections.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Sección eliminada exitosamente.'
            ]);
    }
}
