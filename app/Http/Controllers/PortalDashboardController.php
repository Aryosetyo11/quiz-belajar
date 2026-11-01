<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Subject;
use App\Models\SubjectMaterial;
use App\Models\User;
use Illuminate\View\View;

class PortalDashboardController extends Controller
{
    public function teacher(): View
    {
        $teacherId = auth()->id();

        $subjects = Subject::query()
            ->where('teacher_id', $teacherId)
            ->withCount([
                'students',
                'materials',
                'exams as exams_count' => fn ($q) => $q->where('type', Exam::TYPE_SESSION)->whereNull('archived_at'),
                'exams as active_exams_count' => fn ($q) => $q->where('type', Exam::TYPE_SESSION)->whereNull('archived_at')->where('is_published', true),
            ])
            ->orderByDesc('students_count')
            ->orderBy('name')
            ->get();

        $activeExams = Exam::query()
            ->whereHas('subject', fn ($q) => $q->where('teacher_id', $teacherId))
            ->where('type', Exam::TYPE_SESSION)
            ->where('is_published', true)
            ->whereNull('archived_at')
            ->with(['subject:id,name'])
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->limit(5)
            ->get();

        $allSubmittedAttemptsQuery = ExamAttempt::query()
            ->whereHas('exam', fn ($q) => $q->where('type', Exam::TYPE_SESSION)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('teacher_id', $teacherId)))
            ->whereNotNull('submitted_at');

        $totalSubmissions = (clone $allSubmittedAttemptsQuery)->count();
        $scoredAttemptsQuery = (clone $allSubmittedAttemptsQuery)->whereNotNull('score');
        $scoredAttemptCount = (clone $scoredAttemptsQuery)->count();
        $averageScore = $totalSubmissions > 0
            ? round((float) (clone $allSubmittedAttemptsQuery)->avg('score'), 1)
            : null;

        $passedCount = $scoredAttemptCount > 0
            ? (clone $scoredAttemptsQuery)->where('score', '>=', 70)->count()
            : 0;
        $passRate = $scoredAttemptCount > 0
            ? (int) round(($passedCount / $scoredAttemptCount) * 100)
            : null;

        $remedialCount = $scoredAttemptCount > 0
            ? (clone $scoredAttemptsQuery)->where('score', '<', 70)->count()
            : 0;

        $violationAlertCount = (clone $allSubmittedAttemptsQuery)
            ->where('is_terminated_by_system', true)
            ->count();

        $recentAttempts = ExamAttempt::query()
            ->whereHas('exam', fn ($q) => $q->where('type', Exam::TYPE_SESSION)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('teacher_id', $teacherId)))
            ->whereNotNull('submitted_at')
            ->with(['student:id,name,nisn', 'exam:id,title,subject_id', 'exam.subject:id,name'])
            ->latest('submitted_at')
            ->limit(6)
            ->get();

        $totalMaterials = SubjectMaterial::query()
            ->whereHas('subject', fn ($q) => $q->where('teacher_id', $teacherId))
            ->count();

        return view('portal.dashboard', [
            'role' => User::ROLE_TEACHER,
            'teacherSubjects' => $subjects,
            'subjectCount' => $subjects->count(),
            'studentEnrollmentCount' => $subjects->sum('students_count'),
            'totalMaterials' => $totalMaterials,
            'largestSubjectEnrollment' => max(1, (int) $subjects->max('students_count')),
            'activeExams' => $activeExams,
            'recentAttempts' => $recentAttempts,
            'totalSubmissions' => $totalSubmissions,
            'averageScore' => $averageScore,
            'passRate' => $passRate,
            'remedialCount' => $remedialCount,
            'violationAlertCount' => $violationAlertCount,
        ]);
    }

    public function student(): View
    {
        $subjects = auth()->user()
            ->enrolledSubjects()
            ->with('teacher:id,name')
            ->withCount([
                'materials',
                'materials as documents_count' => fn ($query) => $query->where('type', 'file'),
                'materials as videos_count' => fn ($query) => $query->where('type', 'video'),
                'materials as links_count' => fn ($query) => $query->where('type', 'link'),
            ])
            ->orderBy('subjects.name')
            ->get();

        $allExams = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->whereHas('subject', fn ($q) => $q->whereHas('students', fn ($sq) => $sq->where('users.id', auth()->id())))
            ->where('is_published', true)
            ->whereNull('archived_at')
            ->with(['subject:id,name', 'attempts' => fn ($q) => $q->where('student_id', auth()->id())])
            ->withCount('questions')
            ->latest()
            ->get();

        $ongoingExams = $allExams->filter(fn ($exam) => $exam->attempts->first()?->submitted_at === null
            && $exam->attempts->isNotEmpty()
            && ! $exam->attempts->first()->hasExpired($exam));
        $expiredExams = $allExams->filter(fn ($exam) => $exam->attempts->first()?->submitted_at === null
            && $exam->attempts->isNotEmpty()
            && $exam->attempts->first()->hasExpired($exam));
        $availableExams = $allExams->filter(fn ($exam) => $exam->attempts->isEmpty()
            && ($exam->starts_at === null || $exam->starts_at->isPast())
            && ($exam->ends_at === null || $exam->ends_at->isFuture()));
        $scheduledExams = $allExams->filter(fn ($exam) => $exam->attempts->isEmpty()
            && $exam->starts_at?->isFuture()
            && ($exam->ends_at === null || $exam->ends_at->isFuture()));
        $completedExams = $allExams
            ->filter(fn ($exam) => $exam->attempts->first()?->submitted_at !== null)
            ->sortByDesc(fn ($exam) => $exam->attempts->first()?->submitted_at);

        $scoredExams = $completedExams->filter(fn ($exam) => $exam->attempts->first()?->score !== null);
        $averageScore = $scoredExams->isNotEmpty()
            ? round($scoredExams->avg(fn ($exam) => $exam->attempts->first()?->score), 1)
            : null;

        $dashboardExams = $ongoingExams
            ->concat($availableExams)
            ->concat($expiredExams)
            ->concat($scheduledExams->sortBy('starts_at'))
            ->values();

        return view('portal.dashboard', [
            'role' => User::ROLE_STUDENT,
            'studentSubjects' => $subjects,
            'studentSubjectCount' => $subjects->count(),
            'pendingExams' => $dashboardExams->take(4),
            'completedExams' => $completedExams->values()->take(3),
            'averageScore' => $averageScore,
            'totalPendingCount' => $ongoingExams->count() + $availableExams->count(),
            'scheduledExamCount' => $scheduledExams->count(),
            'totalCompletedCount' => $completedExams->count(),
        ]);
    }
}
