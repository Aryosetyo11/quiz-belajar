<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentExamController extends Controller
{
    public function index(Request $request): View
    {
        $exams = Exam::query()
            ->where('is_published', true)
            ->whereIn('subject_id', $request->user()->enrolledSubjects()->select('subjects.id'))
            ->with(['subject:id,name', 'attempts' => fn ($query) => $query->where('student_id', $request->user()->id)])
            ->withCount('questions')
            ->latest()
            ->get();

        return view('portal.student-exams', ['exams' => $exams]);
    }

    public function show(Request $request, Exam $exam): View|RedirectResponse
    {
        $this->ensureStudentCanAccess($request, $exam);
        $attempt = $exam->attempts()->where('student_id', $request->user()->id)->first();

        if ($attempt === null) {
            return view('portal.student-exam-intro', ['exam' => $exam->load('subject')->loadCount('questions')]);
        }

        if ($attempt->submitted_at !== null) {
            return view('portal.student-exam-result', ['exam' => $exam, 'attempt' => $attempt]);
        }

        if ($this->hasExpired($exam, $attempt)) {
            return redirect()->route('student.exams')->withErrors(['exam' => 'Waktu ujian Anda telah habis. Hubungi guru untuk tindak lanjut.']);
        }

        return view('portal.student-exam', ['exam' => $exam->load('questions'), 'attempt' => $attempt]);
    }

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureStudentCanAccess($request, $exam);
        if (! $this->isAvailable($exam)) {
            return redirect()->route('student.exams')->withErrors(['exam' => 'Ujian ini belum dibuka atau masa pengerjaannya telah berakhir.']);
        }

        $exam->attempts()->firstOrCreate(
            ['student_id' => $request->user()->id],
            ['started_at' => now()],
        );

        return redirect()->route('student.exams.show', $exam);
    }

    public function submit(Request $request, Exam $exam): RedirectResponse
    {
        $this->ensureStudentCanAccess($request, $exam);
        $attempt = $exam->attempts()->where('student_id', $request->user()->id)->firstOrFail();

        if ($attempt->submitted_at !== null) {
            return redirect()->route('student.exams.show', $exam);
        }
        if ($this->hasExpired($exam, $attempt)) {
            return back()->withErrors(['exam' => 'Waktu ujian telah habis. Jawaban tidak dapat dikirim.']);
        }

        $questions = $exam->questions()->get();
        $validAnswers = $questions->mapWithKeys(fn ($question) => [(string) $question->id => array_keys($question->options)])->all();
        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['required', 'string'],
        ]);
        $answers = $validated['answers'] ?? [];
        foreach ($answers as $questionId => $answer) {
            if (! isset($validAnswers[$questionId]) || ! in_array($answer, $validAnswers[$questionId], true)) {
                return back()->withErrors(['answers' => 'Ada jawaban yang tidak valid. Muat ulang ujian lalu coba lagi.']);
            }
        }

        $totalPoints = (int) $questions->sum('points');
        $earnedPoints = (int) $questions->sum(fn ($question) => ($answers[(string) $question->id] ?? null) === $question->answer_key ? $question->points : 0);
        $score = $totalPoints > 0 ? (int) round(($earnedPoints / $totalPoints) * 100) : 0;

        DB::transaction(function () use ($attempt, $answers, $score): void {
            $attempt->forceFill(['answers' => $answers, 'score' => $score, 'submitted_at' => now()])->save();
        });

        return redirect()->route('student.exams.show', $exam)->with('status', 'Jawaban berhasil dikirim.');
    }

    private function ensureStudentCanAccess(Request $request, Exam $exam): void
    {
        abort_unless($exam->is_published, 404);
        abort_unless($request->user()->enrolledSubjects()->whereKey($exam->subject_id)->exists(), 403);
    }

    private function isAvailable(Exam $exam): bool
    {
        return ($exam->starts_at === null || $exam->starts_at->isPast())
            && ($exam->ends_at === null || $exam->ends_at->isFuture());
    }

    private function hasExpired(Exam $exam, ExamAttempt $attempt): bool
    {
        $deadline = $attempt->started_at->copy()->addMinutes($exam->duration_minutes);

        return $deadline->isPast() || ($exam->ends_at !== null && $exam->ends_at->isPast());
    }
}
