<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;

class SectionStudentController extends Controller
{
    /**
     * Enroll a student in a section.
     */
    public function store(Request $request, Section $section)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
        ]);

        // Verificar que no esté ya inscrito
        if ($section->students()->where('student_id', $validated['student_id'])->exists()) {
            return back()->with('error', 'El estudiante ya está inscrito en esta sección.');
        }

        // Verificar capacidad
        if ($section->enrolled_count >= $section->max_students) {
            return back()->with('error', 'La sección ha alcanzado su capacidad máxima.');
        }

        $section->students()->attach($validated['student_id'], [
            'enrollment_date' => now(),
            'status' => 'enrolled',
        ]);

        return back()->with('success', 'Estudiante inscrito exitosamente.');
    }

    /**
     * Remove a student from a section.
     */
    public function destroy(Section $section, User $student)
    {
        $section->students()->detach($student->id);

        return back()->with('success', 'Estudiante retirado de la sección.');
    }
}
