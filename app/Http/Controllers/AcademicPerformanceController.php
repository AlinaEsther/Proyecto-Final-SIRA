<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AcademicPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search', '');

        // Filtros avanzados
        $studentId = $request->input('student_id');
        $courseId = $request->input('course_id');
        $activityType = $request->input('activity_type');
        $minGrade = $request->input('min_grade');
        $maxGrade = $request->input('max_grade');
        $selectedPeriod = $request->input('period', null);

        $grades = Grade::with(['student.person', 'activity.section.course', 'activity'])
            ->when($user->isStudent(), fn($q) => $q->where('student_id', $user->id))
            ->when($user->isProfessor(), function($q) use ($user) {
                $q->whereHas('activity.section', fn($sq) => $sq->where('professor_id', $user->id));
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->whereHas('student.person', fn($pq) => $pq->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('activity', fn($aq) => $aq->where('title', 'like', "%{$search}%"));
                });
            })
            // Filtro por estudiante
            ->when($studentId, fn($q) => $q->where('student_id', $studentId))
            // Filtro por curso
            ->when($courseId, function($q) use ($courseId) {
                $q->whereHas('activity.section', fn($sq) => $sq->where('course_id', $courseId));
            })
            // Filtro por tipo de actividad
            ->when($activityType, function($q) use ($activityType) {
                $q->whereHas('activity', fn($aq) => $aq->where('type', $activityType));
            })
            // Filtro por rango de calificaciones
            ->when($minGrade !== null, fn($q) => $q->where('points_earned', '>=', $minGrade))
            ->when($maxGrade !== null, fn($q) => $q->where('points_earned', '<=', $maxGrade))
            ->latest()
            ->get();

        // Rendimiento detallado: Para estudiantes, admin, y profesores (filtrado a SUS cursos)
        // Histórico académico: SOLO para estudiantes y admin (información privada completa)
        $detailedPerformance = [];
        $academicHistory = [];

        if ($user->isStudent() || $user->isAdmin() || $user->isProfessor()) {
            // Profesores verán solo cursos donde ellos son profesores (filtro en getDetailedPerformance)
            $detailedPerformance = $this->getDetailedPerformance($user, $studentId, $selectedPeriod);
        }

        if ($user->isStudent() || $user->isAdmin()) {
            // Histórico completo solo para estudiante y admin
            $academicHistory = $this->getAcademicHistory($user, $studentId);
        }

        // Datos para filtros (solo si no es estudiante)
        $filterData = [];
        if (!$user->isStudent()) {
            // Estudiantes: solo de las secciones del profesor (si es profesor)
            $studentsQuery = User::students()->with('person');
            if ($user->isProfessor()) {
                $studentsQuery->whereHas('sectionsAsStudent.professor', function($q) use ($user) {
                    $q->where('id', $user->id);
                });
            }

            // Cursos: solo donde el profesor tiene secciones (si es profesor)
            $coursesQuery = Course::select('id', 'code', 'name')->orderBy('name');
            if ($user->isProfessor()) {
                $coursesQuery->whereHas('sections', fn($q) => $q->where('professor_id', $user->id));
            }

            // Periodos: solo de las secciones del profesor (si es profesor)
            $periodsQuery = \DB::table('sections');
            if ($user->isProfessor()) {
                $periodsQuery->where('professor_id', $user->id);
            }

            $filterData = [
                'students' => $studentsQuery->get()->map(fn($user) => [
                    'id' => $user->id,
                    'person' => [
                        'full_name' => $user->person?->full_name ?? 'Sin nombre'
                    ]
                ]),
                'courses' => $coursesQuery->get(),
                'availablePeriods' => $periodsQuery
                    ->distinct()
                    ->pluck('academic_period')
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray(),
            ];
        } else {
            // Para estudiantes, obtener sus periodos
            $filterData = [
                'availablePeriods' => \DB::table('section_student')
                    ->join('sections', 'section_student.section_id', '=', 'sections.id')
                    ->where('section_student.student_id', $user->id)
                    ->distinct()
                    ->pluck('sections.academic_period')
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray(),
            ];
        }

        return Inertia::render('AcademicPerformance/Index', [
            'grades' => $grades,
            'detailedPerformance' => $detailedPerformance,
            'academicHistory' => $academicHistory,
            'filters' => $request->only(['search', 'student_id', 'course_id', 'activity_type', 'min_grade', 'max_grade', 'period']),
            ...$filterData,
        ]);
    }

    /**
     * Obtiene el rendimiento detallado agrupado por curso y periodo
     */
    private function getDetailedPerformance($user, $studentId = null, $selectedPeriod = null)
    {
        $targetStudentId = $user->isStudent() ? $user->id : $studentId;

        if (!$targetStudentId) {
            return [];
        }

        $enrollments = \DB::table('section_student')
            ->join('sections', 'section_student.section_id', '=', 'sections.id')
            ->join('courses', 'sections.course_id', '=', 'courses.id')
            ->where('section_student.student_id', $targetStudentId)
            ->when($selectedPeriod, fn($q) => $q->where('sections.academic_period', $selectedPeriod))
            // FILTRO CONTEXTUAL: Profesores solo ven cursos donde ELLOS son profesores de ese estudiante
            ->when($user->isProfessor(), function($q) use ($user) {
                $q->where('sections.professor_id', $user->id);
            })
            ->select(
                'sections.id as section_id',
                'sections.academic_period',
                'courses.id as course_id',
                'courses.code as course_code',
                'courses.name as course_name',
                'section_student.grade_p1',
                'section_student.grade_p2',
                'section_student.grade_p3',
                'section_student.grade_exam',
                'section_student.final_grade'
            )
            ->get();

        return $enrollments->map(function($enrollment) {
            // Contar actividades por periodo
            $activitiesCounts = \DB::table('activities')
                ->where('section_id', $enrollment->section_id)
                ->selectRaw('period, count(*) as count')
                ->groupBy('period')
                ->get()
                ->keyBy('period');

            return [
                'section_id' => $enrollment->section_id,
                'period' => $enrollment->academic_period,
                'code' => $enrollment->course_code,
                'course_name' => $enrollment->course_name,
                'assignments' => $activitiesCounts->sum('count'),
                'p1' => $enrollment->grade_p1 ? round($enrollment->grade_p1, 1) : '--',
                'p2' => $enrollment->grade_p2 ? round($enrollment->grade_p2, 1) : '--',
                'p3' => $enrollment->grade_p3 ? round($enrollment->grade_p3, 1) : '--',
                'final' => $enrollment->grade_exam ? round($enrollment->grade_exam, 1) : '--',
                'total' => $enrollment->final_grade ? round($enrollment->final_grade, 1) : '--',
                'absences' => 0, // TODO: Implementar sistema de asistencia
                'allowed_absences' => 3,
            ];
        })->toArray();
    }

    /**
     * Obtiene el histórico académico completo agrupado por periodos
     */
    private function getAcademicHistory($user, $studentId = null)
    {
        $targetStudentId = $user->isStudent() ? $user->id : $studentId;

        if (!$targetStudentId) {
            return [];
        }

        // Obtener el estudiante con su información académica
        $student = User::with('person.academicProgram')->find($targetStudentId);

        if (!$student || !$student->person) {
            return [];
        }

        $enrollments = \DB::table('section_student')
            ->join('sections', 'section_student.section_id', '=', 'sections.id')
            ->join('courses', 'sections.course_id', '=', 'courses.id')
            ->where('section_student.student_id', $targetStudentId)
            ->select(
                'sections.academic_period',
                'courses.code as course_code',
                'courses.name as course_name',
                'courses.credits',
                'section_student.final_grade',
                'section_student.letter_grade',
                'section_student.status'
            )
            ->orderBy('sections.academic_period')
            ->get();

        // Agrupar por periodo
        $groupedByPeriod = $enrollments->groupBy('academic_period');

        return $groupedByPeriod->map(function($courses, $period) use ($student) {
            $totalCredits = $courses->sum('credits');

            // Calcular índice del periodo basado en puntos por letra
            $letterToPoints = [
                'A' => 4.0, 'A-' => 3.7,
                'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
                'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
                'D' => 1.0, 'F' => 0.0
            ];

            $totalPoints = 0;
            foreach ($courses as $course) {
                $points = $letterToPoints[$course->letter_grade] ?? 0;
                $totalPoints += $points * $course->credits;
            }

            $periodIndex = $totalCredits > 0 ? round($totalPoints / $totalCredits, 2) : 0;

            // Determinar condición académica basada en índice
            $condition = $periodIndex >= 2.0 ? 'Normal' : ($periodIndex >= 1.5 ? 'Advertencia' : 'Probatoria');

            return [
                'period' => $period,
                'career' => $student->person->academicProgram->name ?? 'N/A',
                'condition' => $condition,
                'credits' => $totalCredits,
                'gpa' => $periodIndex,
                'courses' => $courses->map(fn($c) => [
                    'code' => $c->course_code,
                    'name' => $c->course_name,
                    'credits' => $c->credits,
                    'grade' => $c->final_grade ? round($c->final_grade, 0) : '--',
                    'letter' => $c->letter_grade ?? '--',
                    'points' => $c->credits * ($c->final_grade ?? 0),
                ])->values()->toArray(),
            ];
        })->values()->toArray();
    }
}
