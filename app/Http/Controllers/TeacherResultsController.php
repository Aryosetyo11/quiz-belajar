<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\ExamQuestion;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TeacherResultsController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = $request->user()->id;
        $sessions = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->whereHas('subject', fn ($query) => $query->where('teacher_id', $teacherId))
            ->with('subject:id,name')
            ->orderBy('title')
            ->get(['id', 'title', 'subject_id']);

        $requestedExamId = $request->query('exam_id');
        $selectedExamId = is_numeric($requestedExamId) && $sessions->contains('id', (int) $requestedExamId)
            ? (int) $requestedExamId
            : null;
        $status = in_array($request->query('status'), ['pending', 'graded', 'automatic', 'all'], true)
            ? (string) $request->query('status')
            : 'pending';
        $search = trim((string) $request->query('search'));

        $attemptsQuery = ExamAttempt::query()
            ->whereNotNull('submitted_at')
            ->whereHas('exam', fn ($query) => $query
                ->where('type', Exam::TYPE_SESSION)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('teacher_id', $teacherId)))
            ->with([
                'student:id,name,nisn',
                'exam:id,title,subject_id,bank_package_id,question_count,essay_question_count',
                'exam.subject:id,name',
                'exam.questions:id,exam_id,type',
            ]);

        if ($selectedExamId !== null) {
            $attemptsQuery->where('exam_id', $selectedExamId);
        }

        if ($search !== '') {
            $attemptsQuery->whereHas('student', fn ($query) => $query
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('nisn', 'like', '%'.$search.'%'));
        }

        $pendingQuery = $this->pendingEssayReviewQuery(clone $attemptsQuery);
        $gradedQuery = (clone $attemptsQuery)->whereNotNull('graded_at');
        $automaticQuery = (clone $attemptsQuery)
            ->whereNull('graded_at')
            ->whereNotNull('score')
            ->whereDoesntHave('exam', fn ($query) => $query
                ->where('essay_question_count', '>', 0)
                ->orWhereHas('questions', fn ($questionQuery) => $questionQuery->where('type', ExamQuestion::TYPE_ESSAY)));

        if ($status === 'pending') {
            $attemptsQuery = $pendingQuery;
        } elseif ($status === 'graded') {
            $attemptsQuery = $gradedQuery;
        } elseif ($status === 'automatic') {
            $attemptsQuery = $automaticQuery;
        }

        return view('portal.teacher-results', [
            'sessions' => $sessions,
            'selectedExamId' => $selectedExamId,
            'selectedStatus' => $status,
            'search' => $search,
            'attempts' => $attemptsQuery->latest('submitted_at')->paginate(15)->withQueryString(),
            'pendingCount' => $pendingQuery->count(),
            'gradedCount' => $gradedQuery->count(),
            'automaticCount' => $automaticQuery->count(),
        ]);
    }

    public function show(Request $request, ExamAttempt $attempt): View
    {
        $attempt = $this->ownedSubmittedAttempt($request, $attempt);
        $questions = $this->questionsForAttempt($attempt);

        return view('portal.teacher-result-review', [
            'attempt' => $attempt,
            'questions' => $questions,
            'essayQuestions' => $questions->filter(fn ($question) => $question->isEssay())->values(),
        ]);
    }

    public function grade(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        $attempt = $this->ownedSubmittedAttempt($request, $attempt);
        $questions = $this->questionsForAttempt($attempt);
        $essayQuestions = $questions->filter(fn ($question) => $question->isEssay());

        abort_if($essayQuestions->isEmpty(), 404);

        $rules = ['essay_scores' => ['required', 'array']];
        foreach ($essayQuestions as $question) {
            $rules['essay_scores.'.$question->id] = ['required', 'integer', 'between:0,'.$question->points];
            $rules['essay_feedback.'.$question->id] = ['nullable', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);
        $essayScores = [];
        $essayFeedback = [];
        foreach ($essayQuestions as $question) {
            $essayScores[(string) $question->id] = (int) $validated['essay_scores'][$question->id];
            $essayFeedback[(string) $question->id] = trim((string) ($validated['essay_feedback'][$question->id] ?? ''));
        }

        $answers = $attempt->answers ?? [];
        $multipleChoicePoints = $questions
            ->filter(fn ($question) => $question->isMultipleChoice())
            ->sum(fn ($question) => ($answers[(string) $question->id] ?? null) === $question->answer_key ? $question->points : 0);
        $earnedPoints = $multipleChoicePoints + array_sum($essayScores);
        $totalPoints = (int) $questions->sum('points');
        $score = $totalPoints > 0 ? (int) round(($earnedPoints / $totalPoints) * 100) : 0;

        $attempt->forceFill([
            'essay_scores' => $essayScores,
            'essay_feedback' => $essayFeedback,
            'score' => $score,
            'graded_at' => now(),
            'graded_by' => $request->user()->id,
        ])->save();

        return redirect()->route('teacher.results.review', $attempt)
            ->with('status', 'Penilaian tersimpan. Nilai akhir siswa: '.$score.'/100.');
    }

    private function ownedSubmittedAttempt(Request $request, ExamAttempt $attempt): ExamAttempt
    {
        return ExamAttempt::query()
            ->whereKey($attempt->id)
            ->whereNotNull('submitted_at')
            ->whereHas('exam', fn ($query) => $query
                ->where('type', Exam::TYPE_SESSION)
                ->whereHas('subject', fn ($subjectQuery) => $subjectQuery->where('teacher_id', $request->user()->id)))
            ->with(['student:id,name,nisn', 'exam.subject:id,name', 'exam.bankPackage:id,title'])
            ->firstOrFail();
    }

    private function pendingEssayReviewQuery(Builder $query): Builder
    {
        return $query
            ->whereNull('graded_at')
            ->where(function (Builder $attemptQuery): void {
                $attemptQuery
                    ->whereNull('score')
                    ->orWhereHas('exam', fn ($examQuery) => $examQuery
                        ->where('essay_question_count', '>', 0)
                        ->orWhereHas('questions', fn ($questionQuery) => $questionQuery->where('type', ExamQuestion::TYPE_ESSAY)));
            });
    }

    private function questionsForAttempt(ExamAttempt $attempt): Collection
    {
        $exam = $attempt->exam;
        $questionIds = $attempt->question_ids ?? [];

        if ($questionIds === []) {
            return $exam->questions()->get();
        }

        $questions = $exam->bank_package_id !== null
            ? $exam->bankPackage->questions()->whereIn('id', $questionIds)->get()
            : $exam->questions()->whereIn('id', $questionIds)->get();

        return $questions->sortBy(fn ($question) => array_search($question->id, $questionIds, true))->values();
    }
}
