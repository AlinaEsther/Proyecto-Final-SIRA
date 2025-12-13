<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Grade;
use App\Models\Recommendation;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('Estudiante')) {
            return $this->studentDashboard($user);
        } elseif ($user->hasRole('Profesor')) {
            return $this->professorDashboard($user);
        } else {
            return $this->adminDashboard($user);
        }
    }

    private function studentDashboard($user)
    {
        // Secciones activas donde está inscrito
        $activeSections = Section::where('status', 'open')
            ->whereHas('students', fn($q) => $q->where('student_id', $user->id))
            ->with(['course', 'professor.person'])
            ->get()
            ->map(function($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->course->name,
                    'code' => $section->course->code,
                    'professor' => $section->professor?->person?->full_name ?? 'Sin profesor',
                ];
            });

        // Calificaciones recientes
        $recentGrades = Grade::where('student_id', $user->id)
            ->with(['activity.section.course'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($grade) {
                $percentage = ($grade->points_earned && $grade->activity?->max_points)
                    ? round(($grade->points_earned / $grade->activity->max_points) * 100, 2)
                    : 0;

                return [
                    'course' => $grade->activity?->section?->course?->name ?? 'N/A',
                    'activity' => $grade->activity?->title ?? 'N/A',
                    'score' => $grade->points_earned,
                    'max_points' => $grade->activity?->max_points ?? 0,
                    'percentage' => $percentage,
                    'date' => $grade->created_at->format('d/m/Y'),
                ];
            });

        // Promedio general
        $averageGrade = Grade::where('student_id', $user->id)
            ->whereHas('activity', fn($q) => $q->whereNotNull('max_points'))
            ->get()
            ->map(function($grade) {
                return ($grade->points_earned && $grade->activity?->max_points)
                    ? ($grade->points_earned / $grade->activity->max_points) * 100
                    : 0;
            })
            ->avg();

        // Recomendaciones pendientes
        $pendingRecommendations = Recommendation::where('student_id', $user->id)
            ->where('status', 'pending')
            ->with(['material', 'section.course'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($rec) {
                return [
                    'id' => $rec->id,
                    'book_title' => $rec->book_title,
                    'course' => $rec->section?->course?->name ?? $rec->course_name,
                    'date' => $rec->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Dashboard', [
            'role' => 'Estudiante',
            'stats' => [
                'active_sections' => $activeSections->count(),
                'average_grade' => round($averageGrade ?? 0, 2),
                'pending_recommendations' => $pendingRecommendations->count(),
                'completed_activities' => Grade::where('student_id', $user->id)->count(),
            ],
            'activeSections' => $activeSections,
            'recentGrades' => $recentGrades,
            'recommendations' => $pendingRecommendations,
        ]);
    }

    private function professorDashboard($user)
    {
        // Secciones que imparte
        $sections = Section::where('professor_id', $user->id)
            ->where('status', 'open')
            ->with(['course', 'students'])
            ->get();

        $sectionsList = $sections->map(function($section) {
            return [
                'id' => $section->id,
                'name' => $section->course->name . ' - ' . $section->name,
                'students_count' => $section->students->count(),
                'course_code' => $section->course->code,
            ];
        });

        // Estudiantes en total
        $totalStudents = $sections->sum(fn($s) => $s->students->count());

        // Calificaciones pendientes de registrar (actividades sin calificaciones completas)
        $pendingGrades = DB::table('activities')
            ->join('sections', 'activities.section_id', '=', 'sections.id')
            ->leftJoin('grades', 'activities.id', '=', 'grades.activity_id')
            ->where('sections.professor_id', $user->id)
            ->select('activities.id')
            ->groupBy('activities.id')
            ->havingRaw('COUNT(grades.id) < (SELECT COUNT(*) FROM section_student WHERE section_student.section_id = sections.id)')
            ->count();

        // Recomendaciones generadas para mis estudiantes
        $studentIds = $sections->pluck('students')->flatten()->pluck('id')->unique();
        $recommendationsGenerated = Recommendation::whereIn('student_id', $studentIds)->count();

        return Inertia::render('Dashboard', [
            'role' => 'Profesor',
            'stats' => [
                'active_sections' => $sections->count(),
                'total_students' => $totalStudents,
                'pending_grades' => $pendingGrades,
                'recommendations_generated' => $recommendationsGenerated,
            ],
            'sections' => $sectionsList,
        ]);
    }

    private function adminDashboard($user)
    {
        // Estadísticas generales del sistema
        $totalSections = Section::where('status', 'open')->count();
        $totalStudents = User::role('Estudiante')->count();
        $totalProfessors = User::role('Profesor')->count();
        $totalRecommendations = Recommendation::count();

        // Secciones recientes
        $recentSections = Section::where('status', 'open')
            ->with(['course', 'professor.person'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($section) {
                return [
                    'id' => $section->id,
                    'name' => $section->course->name . ' - ' . $section->name,
                    'professor' => $section->professor?->person?->full_name ?? 'Sin asignar',
                    'students_count' => $section->students()->count(),
                ];
            });

        // Recomendaciones recientes
        $recentRecommendations = Recommendation::with(['student.person', 'section.course'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function($rec) {
                return [
                    'id' => $rec->id,
                    'student' => $rec->student?->person?->full_name ?? 'N/A',
                    'book_title' => $rec->book_title,
                    'course' => $rec->section?->course?->name ?? $rec->course_name,
                    'status' => $rec->status,
                    'date' => $rec->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Dashboard', [
            'role' => 'Administrador',
            'stats' => [
                'total_sections' => $totalSections,
                'total_students' => $totalStudents,
                'total_professors' => $totalProfessors,
                'total_recommendations' => $totalRecommendations,
            ],
            'recentSections' => $recentSections,
            'recentRecommendations' => $recentRecommendations,
        ]);
    }
}
