<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search', '');
        $user = $request->user();

        // NO cachear queries paginadas
        $courses = Course::with('academicProgram')
            ->when($user->isProfessor(), function($q) use ($user) {
                // Profesores solo ven cursos donde tienen secciones asignadas
                $q->whereHas('sections', fn($sq) => $sq->where('professor_id', $user->id));
            })
            ->when($search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->academic_program_id, fn($q) => $q->where('academic_program_id', $request->academic_program_id))
            ->when($request->semester, fn($q) => $q->where('semester', $request->semester))
            ->latest()
            ->paginate($perPage);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search', 'academic_program_id', 'semester']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Courses/Create', [
            // Cachear datos auxiliares (selects)
            'academicPrograms' => Cache::remember('academic_programs', 3600, function () {
                return AcademicProgram::select('id', 'name', 'code')
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get();
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses,code|max:20',
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1|max:10',
            'semester' => 'required|integer|min:1|max:12',
            'status' => 'required|in:active,inactive',
        ]);

        $course = Course::create($validated);

        return redirect()->route('courses.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Curso creado exitosamente.'
            ]);
    }

    public function show(Course $course)
    {
        $course->load(['academicProgram', 'sections', 'materials']);

        return Inertia::render('Courses/Show', [
            'course' => $course,
        ]);
    }

    public function edit(Course $course)
    {
        return Inertia::render('Courses/Edit', [
            'course' => $course->load('academicProgram'),
            // Cachear datos auxiliares (selects)
            'academicPrograms' => Cache::remember('academic_programs', 3600, function () {
                return AcademicProgram::select('id', 'name', 'code')
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get();
            }),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'academic_program_id' => 'required|exists:academic_programs,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses,code,' . $course->id,
            'description' => 'nullable|string',
            'credits' => 'required|integer|min:1|max:10',
            'semester' => 'required|integer|min:1|max:12',
            'status' => 'required|in:active,inactive',
        ]);

        $course->update($validated);

        return redirect()->route('courses.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Curso actualizado exitosamente.'
            ]);
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')
            ->with('alert', [
                'type' => 'success',
                'message' => 'Curso eliminado exitosamente.'
            ]);
    }
}
