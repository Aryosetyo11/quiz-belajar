<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentExamController extends Controller
{
    public function index(Request $request): View
    {
        $exams = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->where('is_published', true)
            ->whereNull('archived_at')
            ->whereIn('subject_id', $request->user()->enrolledSubjects()->select('subjects.id'))
            ->with(['subject:id,name', 'attempts' => fn ($query) => $query->where('student_id', $request->user()->id)])
            ->withCount('questions')
            ->latest()
            ->get();

        $exams->each(function (Exam $exam): void {
            $attempt = $exam->attempts->first();

            if ($attempt !== null && $attempt->submitted_at === null && $attempt->hasExpired($exam)) {
                $attempt = $this->finalizeAttempt($exam, $attempt, $attempt->answers ?? [], $attempt->is_terminated_by_system);
                $exam->setRelation('attempts', collect([$attempt]));
            }

            $status = match (true) {
                $attempt?->submitted_at !== null => 'completed',
                $attempt !== null && $attempt->hasExpired($exam) => 'expired',
                $attempt !== null => 'ongoing',
                $exam->ends_at?->isPast() => 'expired',
                $exam->starts_at?->isFuture() => 'scheduled',
                default => 'available',
            };

            $exam->setAttribute('student_status', $status);
        });

        $statusPriority = [
            'ongoing' => 0,
            'available' => 1,
            'scheduled' => 2,
            'completed' => 3,
            'expired' => 4,
        ];
        $exams = $exams
            ->sortBy(fn (Exam $exam): int => $statusPriority[$exam->student_status])
            ->values();
        $statusCounts = $exams->countBy('student_status');

        return view('portal.student-exams', [
            'exams' => $exams,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function show(Request $request, Exam $exam): View|RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);
        $attempt = $exam->attempts()->where('student_id', $request->user()->id)->first();

        if ($exam->isArchived() || ! $exam->is_published) {
            abort_unless($request->user()->enrolledSubjects()->whereKey($exam->subject_id)->exists(), 403);
            abort_unless($attempt?->submitted_at !== null, 404);
        } else {
            $this->ensureStudentCanAccess($request, $exam);
        }

        if ($attempt === null) {
            return view('portal.student-exam-intro', ['exam' => $exam->load('subject')]);
        }

        if ($attempt->submitted_at !== null) {
            $exam->load('subject');
            $essayQuestions = $this->questionsForAttempt($exam, $attempt)
                ->filter(fn ($question) => $question->isEssay())
                ->values();

            return view('portal.student-exam-result', [
                'exam' => $exam,
                'attempt' => $attempt,
                'essayQuestions' => $essayQuestions,
            ]);
        }

        if ($attempt->hasExpired($exam)) {
            $this->finalizeAttempt($exam, $attempt, $attempt->answers ?? [], $attempt->is_terminated_by_system);

            return redirect()->route('student.exams.show', $exam);
        }

        $exam->load('subject');
        $exam->setRelation('questions', $this->questionsForAttempt($exam, $attempt));

        return view('portal.student-exam', ['exam' => $exam, 'attempt' => $attempt]);
    }

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureStudentCanAccess($request, $exam);
        if (! $this->isAvailable($exam)) {
            return redirect()->route('student.exams')->withErrors(['exam' => 'Ujian ini belum dibuka atau masa pengerjaannya telah berakhir.']);
        }

        DB::transaction(function () use ($exam, $request): void {
            $lockedExam = Exam::query()->lockForUpdate()->findOrFail($exam->id);
            $this->ensureStudentCanAccess($request, $lockedExam);

            if (! $this->isAvailable($lockedExam)) {
                abort(404);
            }

            $attempt = $lockedExam->attempts()->where('student_id', $request->user()->id)->first();

            if ($attempt === null) {
                $attempt = $lockedExam->attempts()->create([
                    'student_id' => $request->user()->id,
                    'started_at' => now(),
                    'question_ids' => $this->selectQuestionIds($lockedExam),
                ]);
            } elseif (empty($attempt->question_ids)) {
                $attempt->forceFill(['question_ids' => $this->selectQuestionIds($lockedExam)])->save();
            }
        });

        return redirect()->route('student.exams.show', $exam);
    }

    public function autosave(Request $request, Exam $exam): JsonResponse
    {
        $this->ensureStudentCanAccess($request, $exam);
        $validated = $request->validate([
            'question_id' => ['required', 'integer'],
            'answer' => ['nullable', 'string', 'max:5000'],
        ]);
        $answer = $validated['answer'] ?? '';

        $result = DB::transaction(function () use ($exam, $request, $validated, $answer): array {
            $attempt = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->submitted_at !== null) {
                return ['state' => 'submitted', 'attempt' => $attempt];
            }

            if ($attempt->hasExpired($exam)) {
                return ['state' => 'expired', 'attempt' => $attempt];
            }

            $question = $this->questionsForAttempt($exam, $attempt)
                ->first(fn (ExamQuestion $question): bool => (int) $question->id === (int) $validated['question_id']);

            if ($question === null || ($answer !== '' && $question->isMultipleChoice()
                && ! array_key_exists($answer, $question->options ?? []))) {
                return ['state' => 'invalid', 'attempt' => $attempt];
            }

            $answers = $attempt->answers ?? [];
            if ($answer === '') {
                unset($answers[(string) $question->id]);
            } else {
                $answers[(string) $question->id] = $answer;
            }

            $attempt->forceFill(['answers' => $answers])->save();

            return ['state' => 'saved', 'attempt' => $attempt];
        });

        if ($result['state'] === 'expired') {
            $this->finalizeAttempt($exam, $result['attempt'], $result['attempt']->answers ?? [], $result['attempt']->is_terminated_by_system);
        }

        if ($result['state'] === 'invalid') {
            return response()->json(['message' => 'Jawaban tidak valid. Muat ulang ujian lalu coba lagi.'], 422);
        }

        if ($result['state'] !== 'saved') {
            return response()->json([
                'message' => 'Sesi ujian sudah berakhir.',
                'result_url' => route('student.exams.show', $exam),
            ], 409);
        }

        return response()->json(['saved' => true, 'saved_at' => now()->toIso8601String()]);
    }

    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureStudentCanAccess($request, $exam);
        $attempt = $exam->attempts()->where('student_id', $request->user()->id)->firstOrFail();

        if ($attempt->submitted_at !== null) {
            return redirect()->route('student.exams.show', $exam);
        }
        $questions = $this->questionsForAttempt($exam, $attempt);
        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:5000'],
            'elapsed_seconds' => ['nullable', 'integer', 'between:0,86400'],
        ]);
        $questionsById = $questions->keyBy(fn ($question) => (string) $question->id);
        foreach ($validated['answers'] ?? [] as $questionId => $answer) {
            $question = $questionsById->get((string) $questionId);
            if ($question === null || ($answer !== null && $answer !== '' && $question->isMultipleChoice()
                && ! in_array($answer, array_keys($question->options ?? []), true))) {
                return back()->withErrors(['answers' => 'Ada jawaban yang tidak valid. Muat ulang ujian lalu coba lagi.']);
            }
        }

        $attempt = DB::transaction(function () use ($exam, $attempt, $validated): ExamAttempt {
            $lockedAttempt = ExamAttempt::query()->lockForUpdate()->findOrFail($attempt->id);
            if ($lockedAttempt->submitted_at !== null) {
                return $lockedAttempt;
            }

            $answers = $lockedAttempt->answers ?? [];
            if (! $lockedAttempt->hasExpired($exam)) {
                foreach ($validated['answers'] ?? [] as $questionId => $answer) {
                    if ($answer === null || $answer === '') {
                        unset($answers[(string) $questionId]);
                    } else {
                        $answers[(string) $questionId] = $answer;
                    }
                }
            }

            $maxViolations = max(1, (int) ($exam->max_violations ?? 3));
            $isTerminated = $lockedAttempt->is_terminated_by_system
                || $lockedAttempt->violation_count >= $maxViolations;

            return $this->finalizeLockedAttempt(
                $exam,
                $lockedAttempt,
                $answers,
                $isTerminated,
                isset($validated['elapsed_seconds']) ? (int) $validated['elapsed_seconds'] : null,
            );
        });

        $message = $attempt->is_terminated_by_system
            ? 'Ujian dihentikan otomatis karena melanggar batas sistem proctoring (anti-kecurangan).'
            : 'Jawaban berhasil dikirim.';

        return redirect()->route('student.exams.show', $exam)->with('status', $message);
    }

    public function logViolation(Request $request, Exam $exam): JsonResponse
    {
        $this->ensureStudentCanAccess($request, $exam);
        abort_unless($exam->enable_proctoring, 403);
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $result = DB::transaction(function () use ($exam, $request, $validated): array {
            $attempt = ExamAttempt::query()
                ->where('exam_id', $exam->id)
                ->where('student_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if ($attempt === null || $attempt->submitted_at !== null) {
                return ['state' => 'closed', 'attempt' => $attempt];
            }

            if ($attempt->hasExpired($exam)) {
                $attempt = $this->finalizeLockedAttempt($exam, $attempt, $attempt->answers ?? [], $attempt->is_terminated_by_system);

                return ['state' => 'expired', 'attempt' => $attempt];
            }

            $logs = $attempt->violation_logs ?? [];
            $logs[] = [
                'reason' => $validated['reason'],
                'time' => now()->toIso8601String(),
            ];
            $newCount = ($attempt->violation_count ?? 0) + 1;
            $maxViolations = max(1, (int) ($exam->max_violations ?? 3));
            $answers = $attempt->answers ?? [];

            $attempt->forceFill([
                'violation_count' => $newCount,
                'violation_logs' => $logs,
            ])->save();

            if ($newCount >= $maxViolations) {
                $attempt = $this->finalizeLockedAttempt($exam, $attempt, $answers, true);
            }

            return [
                'state' => 'logged',
                'attempt' => $attempt,
                'max_violations' => $maxViolations,
            ];
        });

        if ($result['state'] === 'closed') {
            return response()->json(['message' => 'Sesi ujian tidak aktif.'], 409);
        }

        if ($result['state'] === 'expired') {
            return response()->json([
                'message' => 'Waktu ujian telah habis.',
                'result_url' => route('student.exams.show', $exam),
            ], 409);
        }

        return response()->json([
            'success' => true,
            'violation_count' => $result['attempt']->violation_count,
            'max_violations' => $result['max_violations'],
            'auto_submitted' => $result['attempt']->submitted_at !== null,
            'result_url' => $result['attempt']->submitted_at !== null ? route('student.exams.show', $exam) : null,
        ]);
    }

    private function finalizeAttempt(Exam $exam, ExamAttempt $attempt, array $answers, bool $isTerminated): ExamAttempt
    {
        return DB::transaction(function () use ($exam, $attempt, $answers, $isTerminated): ExamAttempt {
            $lockedAttempt = ExamAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            if ($lockedAttempt->submitted_at !== null) {
                return $lockedAttempt;
            }

            return $this->finalizeLockedAttempt($exam, $lockedAttempt, $answers, $isTerminated);
        });
    }

    /** @param array<int|string, string> $answers */
    private function finalizeLockedAttempt(Exam $exam, ExamAttempt $attempt, array $answers, bool $isTerminated, ?int $reportedElapsedSeconds = null): ExamAttempt
    {
        $questions = $this->questionsForAttempt($exam, $attempt);
        $multipleChoiceQuestions = $questions->filter(fn (ExamQuestion $question): bool => $question->isMultipleChoice());
        $score = $questions->contains(fn (ExamQuestion $question): bool => $question->isEssay())
            ? null
            : ($multipleChoiceQuestions->sum('points') > 0
                ? (int) round((
                    $multipleChoiceQuestions->sum(fn (ExamQuestion $question): int => (
                        ($answers[(string) $question->id] ?? null) === $question->answer_key
                    ) ? $question->points : 0
                    ) / $multipleChoiceQuestions->sum('points')) * 100)
                : 0);

        $serverElapsedSeconds = max(0, now()->timestamp - $attempt->started_at->timestamp);
        $maximumDurationSeconds = max(0, ($exam->duration_minutes + (int) $attempt->extra_time_minutes) * 60);
        $durationSeconds = min(
            $maximumDurationSeconds,
            max($serverElapsedSeconds, $reportedElapsedSeconds ?? 0),
        );

        $attempt->forceFill([
            'answers' => $answers,
            'score' => $score,
            'duration_seconds' => $durationSeconds,
            'is_terminated_by_system' => $isTerminated,
            'submitted_at' => now(),
        ])->save();

        return $attempt;
    }

    private function ensureStudentCanAccess(Request $request, Exam $exam): void
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);
        abort_unless(! $exam->isArchived(), 404);
        abort_unless($exam->is_published, 404);
        abort_unless($request->user()->enrolledSubjects()->whereKey($exam->subject_id)->exists(), 403);
    }

    private function isAvailable(Exam $exam): bool
    {
        return ($exam->starts_at === null || $exam->starts_at->isPast())
            && ($exam->ends_at === null || $exam->ends_at->isFuture());
    }

    /** @return array<int, int> */
    private function selectQuestionIds(Exam $exam): array
    {
        if ($exam->bank_package_id === null) {
            return $exam->questions()->inRandomOrder()->pluck('id')->all();
        }

        $pool = $exam->bankPackage->questions()->get();
        $essayQuestions = $pool->where('type', ExamQuestion::TYPE_ESSAY)->shuffle();
        $multipleChoiceQuestions = $pool->where('type', ExamQuestion::TYPE_MULTIPLE_CHOICE)->shuffle();
        $essayCount = (int) $exam->essay_question_count;
        $multipleChoiceCount = (int) $exam->question_count - $essayCount;

        abort_if(
            $essayQuestions->count() < $essayCount || $multipleChoiceQuestions->count() < $multipleChoiceCount,
            422,
            'Paket Bank Soal tidak lagi memiliki jumlah soal yang cukup untuk sesi ini.',
        );

        $previousAttempts = $exam->attempts()
            ->whereNotNull('question_ids')
            ->get(['question_ids'])
            ->all();
        $usedQuestionSets = [];
        $questionUsageCounts = [];

        foreach ($previousAttempts as $previousAttempt) {
            $questionIds = $previousAttempt->question_ids ?? [];
            $usedQuestionSets[] = $this->questionSetKey($questionIds);

            foreach ($questionIds as $questionId) {
                $questionUsageCounts[$questionId] = ($questionUsageCounts[$questionId] ?? 0) + 1;
            }
        }

        for ($attempt = 0; $attempt < 100; $attempt++) {
            $questionIds = $essayQuestions->shuffle()
                ->sortBy(fn ($question) => $questionUsageCounts[$question->id] ?? 0)
                ->take($essayCount)
                ->concat($multipleChoiceQuestions->shuffle()
                    ->sortBy(fn ($question) => $questionUsageCounts[$question->id] ?? 0)
                    ->take($multipleChoiceCount))
                ->shuffle()
                ->pluck('id')
                ->all();

            if (! in_array($this->questionSetKey($questionIds), $usedQuestionSets, true)) {
                return $questionIds;
            }
        }

        abort(422, 'Semua kombinasi soal yang tersedia sudah pernah dibagikan. Tambahkan soal baru ke paket Bank Soal.');
    }

    private function questionsForAttempt(Exam $exam, ExamAttempt $attempt): Collection
    {
        $questionIds = $attempt->question_ids ?? [];
        if ($questionIds === []) {
            return $exam->questions()->get();
        }

        $questions = $exam->bank_package_id !== null
            ? $exam->bankPackage->questions()->whereIn('id', $questionIds)->get()
            : $exam->questions()->whereIn('id', $questionIds)->get();

        return $questions->sortBy(fn ($question) => array_search($question->id, $questionIds, true))->values();
    }

    /** @param array<int, int|string> $questionIds */
    private function questionSetKey(array $questionIds): string
    {
        sort($questionIds);

        return implode(',', $questionIds);
    }
}
