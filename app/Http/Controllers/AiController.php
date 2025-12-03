<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Support\Facades\Http;

class AiController extends Controller
{
    public function index(Request $request)
    {
        // Lista de estudiantes (solo usuarios con rol Estudiante y con person)
        $students = User::students()
            ->with('person')
            ->get()
            ->map(function($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->person?->full_name ?? $u->name
                ];
            });

        return Inertia::render('Recommendations/Index', [
            'students' => $students,
        ]);
    }

    public function recommend(Request $request)
    {
        $request->validate([
            'student_id' => 'required|integer'
        ]);

        $studentId = $request->student_id;

        // Confirmar que existe el estudiante
        $student = User::with('person')->find($studentId);
        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        // Obtener calificaciones del estudiante junto a la actividad y curso
        $grades = Grade::with(['activity.section.course'])
            ->where('student_id', $studentId)
            ->get();

        if ($grades->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'El estudiante no tiene calificaciones registradas.'
            ]);
        }

        // Agrupar por curso y listar actividades con nota baja
        // Definimos "baja" como points_earned <= 10 (ajustable)
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

            // Guardar la actividad siempre — luego filtramos por bajo rendimiento
            $byCourse[$courseKey]['activities'][] = [
                'activity_id' => $g->activity?->id,
                'title' => $g->activity?->title ?? 'Actividad desconocida',
                'score' => $score,
                'max_points' => $max,
                // Porcentaje si conocemos max_points
                'percentage' => ($score !== null && $max) ? round(($score / $max) * 100, 2) : null
            ];
        }

        // Filtrar para mantener solo actividades con score <= umbral o porcentaje baja
        $coursesWithProblems = [];
        foreach ($byCourse as $c) {
            $lowActivities = array_filter($c['activities'], function($a) use ($threshold) {
                if ($a['score'] === null) return false;
                // Usamos puntos absolutos y también porcentaje (si está)
                if ($a['score'] <= $threshold) return true;
                if ($a['percentage'] !== null && $a['percentage'] <= 60) return true; // ejemplo 60%
                return false;
            });

            if (count($lowActivities) > 0) {
                $c['activities'] = array_values($lowActivities);
                $coursesWithProblems[] = $c;
            }
        }

        if (empty($coursesWithProblems)) {
            // Si no hay problemas, devolvemos una recomendación general
            return response()->json([
                'status' => 'success',
                'recommendation' => 'El estudiante no presenta materias con bajo rendimiento. Mantener seguimiento y prácticas regulares.'
            ]);
        }

        // Enviar al microservicio IA: lista de cursos con actividades problemáticas
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
                    'message' => 'El servicio IA devolvió un error HTTP.',
                    'detalle' => $response->body()
                ]);
            }

            $json = $response->json();

            // Esperamos un formato: { recommendations: [ { course: "...", book: "...", reason: "..." }, ... ] }
            if (!isset($json['recommendations']) && !isset($json['recommendation'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Respuesta inesperada del servicio IA.',
                    'detalle' => $json
                ]);
            }

            // Normalizar la respuesta para el frontend
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
                // si microservicio devuelve 'recommendation' simple
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
                'message' => 'No se pudo conectar con el servicio IA.',
                'detalle' => $e->getMessage()
            ]);
        }
    }
}
