<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\User;
use App\Services\FoundationReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request, FoundationReportService $reports): View
    {
        $isFoundationAdmin = auth()->user()->role === User::ROLE_ADMIN_FOUNDATION;
        $reportFilters = ['from' => null, 'to' => null, 'school_id' => null];

        if ($isFoundationAdmin) {
            $validatedFilters = $request->validate([
                'from' => ['nullable', 'date'],
                'to' => ['nullable', 'date', 'after_or_equal:from'],
                'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            ]);

            $reportFilters = [
                'from' => $validatedFilters['from'] ?? null,
                'to' => $validatedFilters['to'] ?? null,
                'school_id' => isset($validatedFilters['school_id']) ? (int) $validatedFilters['school_id'] : null,
            ];
        }

        $teacherCount = User::query()->where('role', User::ROLE_TEACHER)->count();
        $studentCount = User::query()->where('role', User::ROLE_STUDENT)->count();
        $adminCount = User::query()->whereIn('role', [User::ROLE_ADMIN_REMOTE, User::ROLE_ADMIN_FOUNDATION])->count();
        $remoteAdminCount = User::query()->where('role', User::ROLE_ADMIN_REMOTE)->count();
        $foundationAdminCount = User::query()->where('role', User::ROLE_ADMIN_FOUNDATION)->count();
        $subjectCount = Subject::query()->count();
        $materialCount = SubjectMaterial::query()->count();
        $enrollmentCount = DB::table('classroom_student')->count();
        $schoolCount = School::query()->count();
        $classroomCount = Classroom::query()->count();
        $schoolSummaries = $reports->schoolOverview($reportFilters['from'], $reportFilters['to']);
        $reportSchools = School::query()->orderBy('name')->get(['id', 'name']);

        if ($isFoundationAdmin && $reportFilters['school_id'] !== null) {
            $schoolSummaries = $schoolSummaries->where('id', $reportFilters['school_id'])->values();
        }

        $totalAccounts = $teacherCount + $studentCount + $adminCount;

        $filteredSchoolId = $isFoundationAdmin ? $reportFilters['school_id'] : null;
        $reportMetrics = $reports->metrics($reportFilters['from'], $reportFilters['to'], $filteredSchoolId);
        $examCount = (int) $reportMetrics->exam_sessions_count;
        $attemptCount = (int) $reportMetrics->submitted_attempts_count;
        $averageScore = $reportMetrics->average_score;
        $averageScore = $averageScore !== null ? round($averageScore, 1) : null;

        $now = now();
        $publishedSessions = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->where('is_published', true);
        $activeSessionCount = (clone $publishedSessions)
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', $now))
            ->count();
        $upcomingSessionCount = (clone $publishedSessions)
            ->where('starts_at', '>', $now)
            ->count();
        $draftSessionCount = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->where('is_published', false)
            ->whereNull('archived_at')
            ->count();
        $pendingEssayReviewCount = ExamAttempt::query()
            ->whereNotNull('submitted_at')
            ->whereNull('score')
            ->whereHas('exam', fn (Builder $query) => $query->where('type', Exam::TYPE_SESSION))
            ->count();
        $studentsWithoutSubjectCount = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->whereDoesntHave('enrolledSubjects')
            ->count();
        $teachersWithoutSubjectCount = User::query()
            ->where('role', User::ROLE_TEACHER)
            ->whereDoesntHave('subjectsAsTeacher')
            ->count();

        $topTeachers = $isFoundationAdmin
            ? User::query()
                ->where('role', User::ROLE_TEACHER)
                ->withCount(['subjectsAsTeacher as student_count' => fn ($q) => $q->join('subject_student', 'subjects.id', '=', 'subject_student.subject_id')])
                ->orderByDesc('student_count')
                ->limit(5)
                ->get(['id', 'name'])
            : collect();

        $chartData = collect([
            ['label' => 'Siswa', 'count' => $studentCount, 'color' => '#f97316'],
            ['label' => 'Guru', 'count' => $teacherCount, 'color' => '#0f766e'],
            ['label' => 'Admin All Remote', 'count' => $remoteAdminCount, 'color' => '#7c3aed'],
            ['label' => 'Admin Yayasan', 'count' => $foundationAdminCount, 'color' => '#2563eb'],
        ])->map(function (array $item) use ($totalAccounts): array {
            $item['percentage'] = $totalAccounts > 0 ? (int) round(($item['count'] / $totalAccounts) * 100) : 0;

            return $item;
        });

        $donutSegments = [];
        $start = 0;

        foreach ($chartData as $item) {
            if ($item['count'] === 0) {
                continue;
            }

            $end = $start + ($item['count'] / $totalAccounts * 100);
            $donutSegments[] = "{$item['color']} {$start}% {$end}%";
            $start = $end;
        }

        return view('portal.admin-dashboard', [
            'teacherCount' => $teacherCount,
            'studentCount' => $studentCount,
            'adminCount' => $adminCount,
            'remoteAdminCount' => $remoteAdminCount,
            'foundationAdminCount' => $foundationAdminCount,
            'subjectCount' => $subjectCount,
            'materialCount' => $materialCount,
            'enrollmentCount' => $enrollmentCount,
            'schoolCount' => $schoolCount,
            'classroomCount' => $classroomCount,
            'schoolSummaries' => $schoolSummaries,
            'reportSchools' => $reportSchools,
            'reportFilters' => $reportFilters,
            'totalAccounts' => $totalAccounts,
            'chartData' => $chartData,
            'donutBackground' => $donutSegments === []
                ? '#e2e8f0'
                : 'conic-gradient('.implode(', ', $donutSegments).')',
            'latestUsers' => $isFoundationAdmin
                ? collect()
                : User::query()->with('school:id,name')->latest('created_at')->limit(6)->get(['id', 'name', 'email', 'nisn', 'role', 'school_id', 'created_at']),
            'isFoundationAdmin' => $isFoundationAdmin,
            'examCount' => $examCount,
            'attemptCount' => $attemptCount,
            'averageScore' => $averageScore,
            'topTeachers' => $topTeachers,
            'activeSessionCount' => $activeSessionCount,
            'upcomingSessionCount' => $upcomingSessionCount,
            'draftSessionCount' => $draftSessionCount,
            'pendingEssayReviewCount' => $pendingEssayReviewCount,
            'studentsWithoutSubjectCount' => $studentsWithoutSubjectCount,
            'teachersWithoutSubjectCount' => $teachersWithoutSubjectCount,
            'recentExamSessions' => $isFoundationAdmin
                ? collect()
                : Exam::query()
                    ->where('type', Exam::TYPE_SESSION)
                    ->whereNull('archived_at')
                    ->with('subject:id,name')
                    ->withCount([
                        'attempts',
                        'attempts as submitted_attempts_count' => fn (Builder $query) => $query->whereNotNull('submitted_at'),
                    ])
                    ->latest()
                    ->limit(5)
                    ->get(),
        ]);
    }
}
