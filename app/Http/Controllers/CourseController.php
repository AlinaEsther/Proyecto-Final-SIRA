<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\AcademicProgram;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::with('academicProgram')
            ->when($request->academic_program_id, fn($q) => $q->where('academic_program_id', $request->academic_program_id))
            ->when($request->semester, fn($q) => $q->where('semester', $request->semester))
            ->latest()
            ->paginate(15);

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['academic_program_id', 'semester']),
        ]);
    }

    public function create()
    {
        $academicPrograms = AcademicProgram::active()->get();

        return Inertia::render('Courses/Create', [
            'academicPrograms' => $academicPrograms,
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

        return redirect()->route('courses.show', $course)
            ->with('success', 'Curso creado exitosamente.');
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
        $academicPrograms = AcademicProgram::active()->get();

        return Inertia::render('Courses/Edit', [
            'course' => $course,
            'academicPrograms' => $academicPrograms,
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

        return redirect()->route('courses.show', $course)
            ->with('success', 'Curso actualizado exitosamente.');
    }

    public function destroy(Course $course)
    {
        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Curso eliminado exitosamente.');
    }
}
