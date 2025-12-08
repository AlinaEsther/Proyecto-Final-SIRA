<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AiController extends Controller
{
 public function index(Request $request)
{
    $user = Auth::user();

    // Si es ESTUDIANTE → cargar sus notas directamente
    if ($user->hasRole('Estudiante')) {

        // Obtener calificaciones con curso y actividad
        $grades = Grade::with(['activity.section.course'])
            ->where('student_id', $user->id)
            ->get();

        // Procesar igual que en recommend()
        $threshold = 10.0;
        $byCourse = [];

        foreach ($grades as $g) {
            $course = $g->activity?->section?->course;
            $courseKey = $course ? $course->id : 'unknown';
            $courseName = $course ? $course->name : 'Asignatura desconocida';

            if (!isset($byCourse[$courseKey])) {
                $byCourse[$courseKey] = [
                    'course_id' => $course ? $course->id : null,
                    'course_name' => $courseName,
                    'activities' => []
                ];
            }

            $score = $g->points_earned !== null ? floatval($g->points_earned) : null;
            $max = $g->activity?->max_points !== null ? floatval($g->activity->max_points) : null;

            $byCourse[$courseKey]['activities'][] = [
                'activity_id' => $g->activity?->id,
                'title' => $g->activity?->title ?? 'Actividad desconocida',
                'score' => $score,
                'max_points' => $max,
                'percentage' => ($score !== null && $max)
                    ? round(($score / $max) * 100, 2)
                    : null
            ];
        }

        return Inertia::render('Recommendations/Index', [
            'isStudent' => true,
            'student' => [
                'id' => $user->id,
                'name' => $user->person?->full_name ?? $user->name,
            ],
            'courses' => array_values($byCourse), // 🔥 ENVIAR CURSOS DIRECTAMENTE
            'students' => []
        ]);
    }

    // Si es PROFESOR/ADMIN → lista de estudiantes
    $students = User::role('Estudiante')
        ->with('person')
        ->get()
        ->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->person?->full_name ?? $u->name
            ];
        });

    return Inertia::render('Recommendations/Index', [
        'isStudent' => false,
        'students' => $students,
        'student' => null,
        'courses' => []
    ]);
}

    public function recommend(Request $request)
{
    $user = Auth::user();

    // Si es estudiante → el backend FORZA su propio ID
    if ($user->hasRole('Estudiante')) {
        $studentId = $user->id;
    } else {
        $request->validate([
            'student_id' => 'required|integer'
        ]);

        $studentId = $request->student_id;
    }

    // Confirmar existencia del estudiante
    $student = User::with('person')->find($studentId);

    if (!$student) {
        return response()->json([
            'status' => 'error',
            'message' => 'Estudiante no encontrado'
        ], 404);
    }

   // Obtener calificaciones con curso y actividad
    $grades = Grade::with(['activity.section.course'])
        ->where('student_id', $studentId)
        ->get();

    if ($grades->isEmpty()) {
        return response()->json([
            'status' => 'error',
            'message' => 'El estudiante no tiene calificaciones registradas.'
        ]);
    }

    $threshold = 10.0;
    $byCourse = [];

    foreach ($grades as $g) {
        $course = $g->activity?->section?->course;
        $courseKey = $course ? $course->id : 'unknown';
        $courseName = $course ? $course->name : 'Asignatura desconocida';

        if (!isset($byCourse[$courseKey])) {
            $byCourse[$courseKey] = [
                'course_id' => $course ? $course->id : null,
                'course_name' => $courseName,
                'activities' => []
            ];
        }

        $score = $g->points_earned !== null ? floatval($g->points_earned) : null;
        $max = $g->activity?->max_points !== null ? floatval($g->activity->max_points) : null;

        $byCourse[$courseKey]['activities'][] = [
            'activity_id' => $g->activity?->id,
            'title' => $g->activity?->title ?? 'Actividad desconocida',
            'score' => $score,
            'max_points' => $max,
            'percentage' => ($score !== null && $max) ? round(($score / $max) * 100, 2) : null
        ];
    }

    $coursesWithProblems = [];
    foreach ($byCourse as $c) {
        $lowActivities = array_filter($c['activities'], function($a) use ($threshold) {
            if ($a['score'] === null) return false;
            if ($a['score'] <= $threshold) return true;
            if ($a['percentage'] !== null && $a['percentage'] <= 60) return true;
            return false;
        });

        if (count($lowActivities) > 0) {
            $c['activities'] = array_values($lowActivities);
            $coursesWithProblems[] = $c;
        }
    }

    if (empty($coursesWithProblems)) {
        return response()->json([
            'status' => 'success',
            'recommendation' => 'No presenta materias con bajo rendimiento.'
        ]);
    }

    try {
        $payload = [
            'student_id' => $studentId,
            'student_name' => $student->person?->full_name ?? $student->name,
            'courses' => $coursesWithProblems
        ];

        $response = Http::timeout(8)->post('http://127.0.0.1:8001/recommend', $payload);

        if (!$response->ok()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error del microservicio IA.',
                'detalle' => $response->body()
            ]);
        }

        $json = $response->json();

        $out = [
            'status' => 'success',
            'student' => [
                'id' => $studentId,
                'name' => $student->person?->full_name ?? $student->name
            ],
        ];

        if (isset($json['recommendations'])) {
            $out['recommendations'] = $json['recommendations'];
        } else {
            $out['recommendations'] = [
                [
                    'course' => 'General',
                    'book' => $json['recommendation']
                ]
            ];
        }

        return response()->json($out);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error conectando al servicio IA.',
            'detalle' => $e->getMessage()
        ]);
    }
}

}
