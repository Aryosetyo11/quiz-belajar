<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = $request->user()->id;
        $sessions = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->whereHas('subject', fn ($query) => $query->where('teacher_id', $teacherId))
            ->with('subject:id,name')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'subject_id', 'is_published']);

        $requestedExamId = $request->query('exam_id');
        $selectedExamId = is_numeric($requestedExamId) && $sessions->contains('id', (int) $requestedExamId)
            ? (int) $requestedExamId
            : null;

        $attemptsQuery = ExamAttempt::query()
            ->whereHas('exam', fn ($query) => $query
                ->where('type', Exam::TYPE_SESSION)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('teacher_id', $teacherId)));

        if ($selectedExamId !== null) {
            $attemptsQuery->where('exam_id', $selectedExamId);
        }

        $withMonitoringRelations = ['student:id,name,nisn', 'exam:id,title,subject_id,duration_minutes,starts_at,ends_at,max_violations,enable_proctoring,is_published', 'exam.subject:id,name'];
        $activeAttempts = (clone $attemptsQuery)
            ->whereNull('submitted_at')
            ->with($withMonitoringRelations)
            ->orderByDesc('violation_count')
            ->orderBy('started_at')
            ->get();

        foreach ($activeAttempts as $attempt) {
            $deadline = $attempt->started_at->copy()->addMinutes($attempt->exam->duration_minutes + (int) $attempt->extra_time_minutes);
            if (empty($attempt->access_restorations) && $attempt->exam->ends_at !== null && $attempt->exam->ends_at->lt($deadline)) {
                $deadline = $attempt->exam->ends_at;
            }

            $attempt->monitoringDeadline = $deadline;
            $attempt->monitoringExpired = $deadline->isPast();
        }

        $flaggedAttempts = (clone $attemptsQuery)
            ->where(function ($query): void {
                $query->where('violation_count', '>', 0)
                    ->orWhere('is_terminated_by_system', true)
                    ->orWhereNotNull('access_restorations');
            })
            ->with($withMonitoringRelations)
            ->latest('updated_at')
            ->limit(30)
            ->get();

        foreach ($flaggedAttempts as $attempt) {
            $attempt->monitoringLogs = collect($attempt->violation_logs ?? [])
                ->map(fn (array $log): array => [
                    'reason' => $log['reason'] ?? 'Aktivitas tercatat',
                    'time' => Carbon::parse($log['time'] ?? $attempt->updated_at),
                ])
                ->sortByDesc('time')
                ->values();
        }

        $needsAttentionAttempts = $flaggedAttempts
            ->filter(fn ($attempt) => $attempt->is_terminated_by_system && $attempt->submitted_at !== null)
            ->values();

        return view('portal.teacher-monitoring', [
            'sessions' => $sessions,
            'selectedExamId' => $selectedExamId,
            'activeAttempts' => $activeAttempts,
            'activeCount' => $activeAttempts->count(),
            'flaggedActiveCount' => $activeAttempts->filter(fn ($attempt) => $attempt->violation_count > 0)->count(),
            'needsAttentionAttempts' => $needsAttentionAttempts,
            'submittedTodayCount' => (clone $attemptsQuery)->whereDate('submitted_at', today())->count(),
            'flaggedAttempts' => $flaggedAttempts,
        ]);
    }

    public function restoreAccess(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $validated = $request->validate([
            'extra_time_minutes' => ['required', 'integer', 'between:1,300'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $ownedAttempt = ExamAttempt::query()
            ->whereKey($attempt->id)
            ->whereHas('exam', fn ($query) => $query
                ->where('type', Exam::TYPE_SESSION)
                ->where('is_published', true)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('teacher_id', $request->user()->id)))
            ->with('exam')
            ->firstOrFail();

        if (! $ownedAttempt->is_terminated_by_system || $ownedAttempt->submitted_at === null) {
            throw ValidationException::withMessages([
                'attempt' => 'Akses kembali hanya bisa diberikan pada pengerjaan yang dihentikan otomatis oleh sistem.',
            ]);
        }

        DB::transaction(function () use ($request, $ownedAttempt, $validated): void {
            $lockedAttempt = ExamAttempt::query()
                ->lockForUpdate()
                ->with('exam')
                ->findOrFail($ownedAttempt->id);

            if (! $lockedAttempt->is_terminated_by_system || $lockedAttempt->submitted_at === null) {
                throw ValidationException::withMessages([
                    'attempt' => 'Pengerjaan ini sudah berubah status dan tidak dapat dibuka kembali.',
                ]);
            }

            $baseDeadline = $lockedAttempt->started_at->copy()->addMinutes($lockedAttempt->exam->duration_minutes);
            $currentDeadline = $baseDeadline->copy()->addMinutes((int) $lockedAttempt->extra_time_minutes);
            if (empty($lockedAttempt->access_restorations)
                && $lockedAttempt->exam->ends_at !== null
                && $lockedAttempt->exam->ends_at->lt($currentDeadline)) {
                $currentDeadline = $lockedAttempt->exam->ends_at;
            }

            $newDeadline = $currentDeadline->isFuture()
                ? $currentDeadline->copy()->addMinutes((int) $validated['extra_time_minutes'])
                : now()->addMinutes((int) $validated['extra_time_minutes']);
            $newExtraTimeMinutes = max(0, (int) ceil(($newDeadline->getTimestamp() - $baseDeadline->getTimestamp()) / 60));
            $restorations = $lockedAttempt->access_restorations ?? [];
            $restorations[] = [
                'granted_by' => $request->user()->id,
                'granted_by_name' => $request->user()->name,
                'granted_at' => now()->toIso8601String(),
                'extra_time_minutes' => (int) $validated['extra_time_minutes'],
                'reason' => $validated['reason'],
                'previous_submitted_at' => $lockedAttempt->submitted_at->toIso8601String(),
                'was_terminated_by_system' => true,
            ];

            $lockedAttempt->forceFill([
                'submitted_at' => null,
                'score' => null,
                'violation_count' => 0,
                'is_terminated_by_system' => false,
                'extra_time_minutes' => $newExtraTimeMinutes,
                'access_restorations' => $restorations,
            ])->save();
        });

        return redirect()
            ->route('teacher.monitoring', ['exam_id' => $ownedAttempt->exam_id])
            ->with('status', 'Akses siswa dibuka kembali. Soal dan jawaban sebelumnya dipertahankan, dengan tambahan '.$validated['extra_time_minutes'].' menit.');
    }
}
