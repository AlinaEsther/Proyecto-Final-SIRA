<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Grade;
use App\Models\Section;
use App\Models\AcademicPeriod;
use App\Models\Recommendation;
use App\Models\Material;
use App\Models\MaterialRequest;
use App\Notifications\RecommendationGenerated;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // ESTUDIANTE → Ver sus propias recomendaciones
        if ($user->hasRole('Estudiante')) {
            return Inertia::render('Recommendations/Index', [
                'isStudent' => true,
                'userRole' => 'Estudiante',
                'student' => [
                    'id' => $user->id,
                    'name' => $user->person?->full_name ?? $user->name,
                ],
                'sections' => [],
                'students' => [],
                'statistics' => $this->getStudentStatistics($user->id),
            ]);
        }

        // PROFESOR → Solo estudiantes de sus secciones
        if ($user->hasRole('Profesor')) {
            $activePeriod = AcademicPeriod::where('is_active', true)->first();
            $activePeriodCode = $activePeriod ? $activePeriod->code : null;

            $sections = Cache::remember('sections_professor_' . $user->id . '_' . $activePeriodCode, 3600, function () use ($user, $activePeriodCode) {
                return Section::where('professor_id', $user->id)
                    ->where('status', 'open')
                    ->when($activePeriodCode, fn($q) => $q->where('academic_period', $activePeriodCode))
                    ->with(['course', 'students.person'])
                    ->get()
                    ->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'name' => $section->name . ' - ' . $section->course->name,
                            'course_name' => $section->course->name,
                            'students' => $section->students->map(function ($student) {
                                return [
                                    'id' => $student->id,
                                    'name' => $student->person?->full_name ?? $student->name,
                                ];
                            })->values(),
                        ];
                    });
            });

            return Inertia::render('Recommendations/Index', [
                'isStudent' => false,
                'userRole' => 'Profesor',
                'sections' => $sections,
                'students' => [],
                'student' => null,
                'statistics' => $this->getProfessorStatistics($user->id),
            ]);
        }

        // ADMIN → Todas las secciones
        $activePeriod = AcademicPeriod::where('is_active', true)->first();
        $activePeriodCode = $activePeriod ? $activePeriod->code : null;

        $sections = Cache::remember('all_sections_admin_' . $activePeriodCode, 1800, function () use ($activePeriodCode) {
            return Section::where('status', 'open')
                ->when($activePeriodCode, fn($q) => $q->where('academic_period', $activePeriodCode))
                ->with(['course', 'professor.person', 'students.person'])
                ->get()
                ->map(function ($section) {
                    return [
                        'id' => $section->id,
                        'name' => $section->name . ' - ' . $section->course->name . ' (' . ($section->professor->person?->full_name ?? 'Sin profesor') . ')',
                        'course_name' => $section->course->name,
                        'students' => $section->students->map(function ($student) {
                            return [
                                'id' => $student->id,
                                'name' => $student->person?->full_name ?? $student->name,
                            ];
                        })->values(),
                    ];
                });
        });

        return Inertia::render('Recommendations/Index', [
            'isStudent' => false,
            'userRole' => 'Administrador',
            'sections' => $sections,
            'students' => [],
            'student' => null,
            'statistics' => $this->getAdminStatistics(),
        ]);
    }

    public function recommend(Request $request)
    {
        $user = Auth::user();

        // Determinar ID del estudiante y sección (opcional para filtrar)
        $sectionFilter = $request->input('section_id'); // Sección opcional para filtrar

        if ($user->hasRole('Estudiante')) {
            $studentId = $user->id;
            $sectionFilter = null; // Estudiantes ven TODAS sus recomendaciones
        } else {
            $request->validate([
                'student_id' => 'required|integer',
                'section_id' => 'nullable|integer|exists:sections,id'
            ]);
            $studentId = $request->student_id;

            // Validar permisos para Profesor
            if ($user->hasRole('Profesor')) {
                $hasAccess = Section::where('professor_id', $user->id)
                    ->whereHas('students', fn($q) => $q->where('student_id', $studentId))
                    ->exists();

                if (!$hasAccess) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No tienes acceso a este estudiante.'
                    ], 403);
                }
            }
        }

        // Obtener estudiante
        $student = User::with('person')->find($studentId);
        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        // Obtener calificaciones agrupadas por sección
        // Si se especifica section_id, filtrar solo esa sección (Admin/Profesor)
        $grades = Grade::with(['activity.section.course', 'activity.section'])
            ->where('student_id', $studentId)
            ->when($sectionFilter, function($query) use ($sectionFilter) {
                // Filtrar calificaciones solo de la sección seleccionada
                $query->whereHas('activity.section', function($q) use ($sectionFilter) {
                    $q->where('id', $sectionFilter);
                });
            })
            ->get();

        if ($grades->isEmpty()) {
            $message = $sectionFilter
                ? 'El estudiante no tiene calificaciones registradas en esta sección.'
                : 'El estudiante no tiene calificaciones registradas.';

            return response()->json([
                'status' => 'error',
                'message' => $message
            ]);
        }

        // Agrupar por sección y calcular promedio
        $bySection = [];
        foreach ($grades as $g) {
            $section = $g->activity?->section;
            if (!$section) continue;

            $sectionId = $section->id;
            $course = $section->course;
            $courseName = $course ? $course->name : 'Asignatura desconocida';

            if (!isset($bySection[$sectionId])) {
                $bySection[$sectionId] = [
                    'section_id' => $sectionId,
                    'course_id' => $course ? $course->id : null,
                    'course_name' => $courseName,
                    'activities' => [],
                    'grades_sum' => 0,
                    'grades_count' => 0,
                ];
            }

            $score = $g->points_earned !== null ? floatval($g->points_earned) : null;
            $max = $g->activity?->max_points !== null ? floatval($g->activity->max_points) : null;
            $percentage = ($score !== null && $max && $max > 0) ? round(($score / $max) * 100, 2) : null;

            if ($percentage !== null) {
                $bySection[$sectionId]['grades_sum'] += $percentage;
                $bySection[$sectionId]['grades_count']++;
            }

            $bySection[$sectionId]['activities'][] = [
                'activity_id' => $g->activity?->id,
                'title' => strip_tags($g->activity?->title ?? 'Actividad desconocida'),
                'score' => $score,
                'max_points' => $max,
                'percentage' => $percentage,
            ];
        }

        // Detectar secciones con bajo rendimiento (promedio < 80%)
        $coursesWithProblems = [];
        foreach ($bySection as $sec) {
            if ($sec['grades_count'] > 0) {
                $average = round($sec['grades_sum'] / $sec['grades_count'], 2);

                if ($average < 80.0) {
                    $sec['average_grade'] = $average;
                    // Filtrar solo actividades con bajo rendimiento individual
                    $lowActivities = array_filter($sec['activities'], function($a) {
                        return $a['percentage'] !== null && $a['percentage'] < 80.0;
                    });

                    if (!empty($lowActivities)) {
                        $sec['activities'] = array_values($lowActivities);
                        $coursesWithProblems[] = $sec;
                    }
                }
            }
        }

        if (empty($coursesWithProblems)) {
            return response()->json([
                'status' => 'success',
                'message' => 'El estudiante tiene buen rendimiento general (≥80%).',
                'student' => [
                    'id' => $studentId,
                    'name' => $student->person?->full_name ?? $student->name
                ],
                'recommendations' => []
            ]);
        }

        // Llamar al microservicio IA
        try {
            $payload = [
                'student_id' => $studentId,
                'student_name' => strip_tags($student->person?->full_name ?? $student->name),
                'courses' => array_map(function ($c) {
                    return [
                        'section_id' => $c['section_id'],
                        'course_name' => strip_tags($c['course_name']),
                        'average_grade' => $c['average_grade'],
                        'activities' => $c['activities'],
                    ];
                }, $coursesWithProblems)
            ];

            $response = Http::timeout(10)->post('http://127.0.0.1:8001/recommend', $payload);

            if (!$response->ok()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error del microservicio IA.',
                    'detalle' => $response->body()
                ], 500);
            }

            $json = $response->json();
            $recommendations = $json['recommendations'] ?? [];

            // Guardar en historial
            $savedRecommendations = [];
            foreach ($recommendations as $idx => $rec) {
                try {
                    $courseData = $coursesWithProblems[$idx] ?? $coursesWithProblems[0];

                    $bookTitle = $rec['book_title'] ?? ($rec['book'] ?? 'Material recomendado');
                    $bookAuthor = $rec['book_author'] ?? null;
                    $bookUrl = $rec['book_url'] ?? '#';

                    // Verificar si el material ya existe en el sistema (por título y autor si existe)
                    $existingMaterial = Material::where('title', $bookTitle)
                        ->when($bookAuthor, function($q) use ($bookAuthor) {
                            return $q->where('author', $bookAuthor);
                        })
                        ->first();

                    if ($existingMaterial) {
                        // Material existe, usar el existente
                        $material = $existingMaterial;
                        $recommendationStatus = 'completed'; // Material ya disponible = completado
                    } else {
                        // Material NO existe - el estudiante deberá solicitarlo usando el botón "Solicitar Material"
                        $material = null;
                        $recommendationStatus = 'not_requested'; // No solicitado aún - esperando que el estudiante haga clic en "Solicitar"
                    }

                    $recommendation = Recommendation::create([
                        'student_id' => $studentId,
                        'section_id' => $courseData['section_id'],
                        'material_id' => $material ? $material->id : null,
                        'generated_by' => $user->id,
                        'course_name' => $rec['course'] ?? $courseData['course_name'],
                        'book_title' => $bookTitle,
                        'book_author' => $bookAuthor,
                        'reason' => substr($rec['reason'] ?? '', 0, 1000),
                        'activities_data' => $rec['activities'] ?? [],
                        'average_grade' => $courseData['average_grade'],
                        'relevance_score' => 85,
                        'status' => $recommendationStatus,
                    ]);

                    // NO crear MaterialRequest automáticamente
                    // El estudiante debe hacer clic en "Solicitar Material" para crear la solicitud

                    $savedRecommendations[] = $recommendation->id;
                    \Log::info('✅ Recomendación guardada: ID=' . $recommendation->id . ', Libro: ' . $bookTitle . ($bookAuthor ? ' por ' . $bookAuthor : ''));
                } catch (\Exception $e) {
                    \Log::error('❌ Error guardando recomendación individual: ' . $e->getMessage());
                    // Continuar con las demás recomendaciones
                }
            }

            // Log de resumen
            \Log::info('📊 Total recomendaciones guardadas en este request: ' . count($savedRecommendations));
            \Log::info('📊 Total recommendations en BD ahora: ' . Recommendation::count());

            // Enviar notificación al estudiante
            try {
                if (!empty($savedRecommendations)) {
                    $student->notify(new RecommendationGenerated($savedRecommendations[0]));
                }
            } catch (\Exception $e) {
                \Log::warning('No se pudo enviar notificación: ' . $e->getMessage());
            }

            // Enriquecer recomendaciones con material_id
            $enrichedRecommendations = array_map(function($rec, $idx) use ($coursesWithProblems) {
                $courseData = $coursesWithProblems[$idx] ?? $coursesWithProblems[0];

                $bookTitle = $rec['book_title'] ?? ($rec['book'] ?? 'Material recomendado');
                $bookAuthor = $rec['book_author'] ?? null;

                // Buscar material existente por título y autor
                $material = Material::where('title', $bookTitle)
                    ->when($bookAuthor, function($q) use ($bookAuthor) {
                        return $q->where('author', $bookAuthor);
                    })
                    ->first();

                $rec['material_id'] = $material ? $material->id : null;
                $rec['material_exists'] = $material !== null;
                $rec['section_id'] = $courseData['section_id'];

                return $rec;
            }, $recommendations, array_keys($recommendations));

            return response()->json([
                'status' => 'success',
                'student' => [
                    'id' => $studentId,
                    'name' => $student->person?->full_name ?? $student->name
                ],
                'recommendations' => $enrichedRecommendations,
                'recommendation_ids' => $savedRecommendations,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en recommend(): ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error conectando al servicio IA.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request, $studentId)
    {
        $user = Auth::user();

        // Validar permisos
        if ($user->hasRole('Estudiante') && $user->id != $studentId) {
            abort(403, 'No autorizado');
        }

        if ($user->hasRole('Profesor')) {
            $hasAccess = Section::where('professor_id', $user->id)
                ->whereHas('students', fn($q) => $q->where('student_id', $studentId))
                ->exists();
            if (!$hasAccess) abort(403, 'No autorizado');
        }

        $recommendations = Recommendation::with(['material', 'section.course', 'generatedBy.person'])
            ->where('student_id', $studentId)
            ->latest()
            ->paginate(15);

        $statistics = [
            'total' => Recommendation::where('student_id', $studentId)->count(),
            'pending' => Recommendation::where('student_id', $studentId)->where('status', 'pending')->count(),
            'completed' => Recommendation::where('student_id', $studentId)->where('status', 'completed')->count(),
            'average_relevance' => round(Recommendation::where('student_id', $studentId)->avg('relevance_score'), 1),
        ];

        return response()->json([
            'recommendations' => $recommendations,
            'statistics' => $statistics,
        ]);
    }

    public function updateStatus(Request $request, Recommendation $recommendation)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'status' => 'required|in:viewed,completed,dismissed',
            'professor_notes' => 'nullable|string|max:1000',
        ]);

        // Validar permisos
        if ($user->hasRole('Estudiante') && $user->id != $recommendation->student_id) {
            abort(403, 'No autorizado');
        }

        if ($user->hasRole('Profesor')) {
            $hasAccess = Section::where('professor_id', $user->id)
                ->where('id', $recommendation->section_id)
                ->exists();
            if (!$hasAccess) abort(403, 'No autorizado');
        }

        $recommendation->update([
            'status' => $validated['status'],
            'professor_notes' => $validated['professor_notes'] ?? $recommendation->professor_notes,
        ]);

        return response()->json([
            'message' => 'Estado actualizado correctamente',
            'recommendation' => $recommendation->fresh(['material', 'section.course']),
        ]);
    }

    public function export($studentId)
    {
        $user = Auth::user();

        // Validar permisos
        if ($user->hasRole('Estudiante') && $user->id != $studentId) {
            abort(403, 'No autorizado');
        }

        if ($user->hasRole('Profesor')) {
            $hasAccess = Section::where('professor_id', $user->id)
                ->whereHas('students', fn($q) => $q->where('student_id', $studentId))
                ->exists();
            if (!$hasAccess) abort(403, 'No autorizado');
        }

        $student = User::with('person')->findOrFail($studentId);
        $recommendations = Recommendation::with(['section.course', 'material', 'generatedBy.person'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();

        $pdf = Pdf::loadView('pdf.recommendations', compact('student', 'recommendations'));

        return $pdf->download('recomendaciones_' . ($student->person?->full_name ?? 'estudiante') . '.pdf');
    }

    public function statistics(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Estudiante')) {
            return response()->json($this->getStudentStatistics($user->id));
        }

        if ($user->hasRole('Profesor')) {
            return response()->json($this->getProfessorStatistics($user->id));
        }

        return response()->json($this->getAdminStatistics());
    }

    private function getStudentStatistics($studentId)
    {
        $thisMonth = now()->startOfMonth();

        return [
            'total_recommendations' => Recommendation::where('student_id', $studentId)->count(),
            'this_month' => Recommendation::where('student_id', $studentId)
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'completed' => Recommendation::where('student_id', $studentId)
                ->where('status', 'completed')
                ->count(),
            'pending' => Recommendation::where('student_id', $studentId)
                ->where('status', 'pending')
                ->count(),
            'completion_rate' => $this->calculateCompletionRate($studentId),
        ];
    }

    private function getProfessorStatistics($professorId)
    {
        $thisMonth = now()->startOfMonth();
        $studentIds = Section::where('professor_id', $professorId)
            ->with('students')
            ->get()
            ->pluck('students')
            ->flatten()
            ->pluck('id')
            ->unique();

        return [
            'total_recommendations' => Recommendation::whereIn('student_id', $studentIds)->count(),
            'this_month' => Recommendation::whereIn('student_id', $studentIds)
                ->where('created_at', '>=', $thisMonth)
                ->count(),
            'students_helped' => Recommendation::whereIn('student_id', $studentIds)
                ->distinct('student_id')
                ->count('student_id'),
            'average_relevance' => round(Recommendation::whereIn('student_id', $studentIds)
                ->avg('relevance_score'), 1),
            'pending_requests' => MaterialRequest::where('status', 'pending')->count(), // Solicitudes de materiales pendientes
        ];
    }

    private function getAdminStatistics()
    {
        $thisMonth = now()->startOfMonth();

        return [
            'total_recommendations' => Recommendation::count(),
            'this_month' => Recommendation::where('created_at', '>=', $thisMonth)->count(),
            'total_students' => Recommendation::distinct('student_id')->count('student_id'),
            'average_relevance' => round(Recommendation::avg('relevance_score'), 1),
            'pending_requests' => MaterialRequest::where('status', 'pending')->count(), // Solicitudes de materiales pendientes
            'by_status' => [
                'not_requested' => Recommendation::where('status', 'not_requested')->count(),
                'pending' => Recommendation::where('status', 'pending')->count(),
                'viewed' => Recommendation::where('status', 'viewed')->count(),
                'completed' => Recommendation::where('status', 'completed')->count(),
                'dismissed' => Recommendation::where('status', 'dismissed')->count(),
            ],
        ];
    }

    private function calculateCompletionRate($studentId)
    {
        $total = Recommendation::where('student_id', $studentId)->count();
        if ($total == 0) return 0;

        $completed = Recommendation::where('student_id', $studentId)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 1);
    }
}

