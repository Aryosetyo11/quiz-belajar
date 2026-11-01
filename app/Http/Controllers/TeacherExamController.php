<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TeacherExamController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = $request->user()
            ->subjectsAsTeacher()
            ->withCount(['exams as bank_package_count' => fn ($query) => $query->where('type', Exam::TYPE_QUESTION_BANK)])
            ->with([
                'school:id,name',
                'classroom:id,name,academic_year',
                'exams' => fn ($query) => $query
                    ->where('type', Exam::TYPE_SESSION)
                    ->withCount('questions', 'attempts')
                    ->withCount(['attempts as active_attempts_count' => fn ($query) => $query->whereNull('submitted_at')])
                    ->withAvg('attempts as average_score', 'score')
                    ->with('bankPackage:id,title')
                    ->with('archivedBy:id,name')
                    ->latest(),
            ])
            ->orderBy('name')
            ->get();

        return view('portal.teacher-exams', ['subjects' => $subjects]);
    }

    public function create(Request $request): View
    {
        $subjectIds = $request->user()->subjectsAsTeacher()->select('id');
        $bankPackages = Exam::query()
            ->where('type', Exam::TYPE_QUESTION_BANK)
            ->whereIn('subject_id', $subjectIds)
            ->with([
                'subject:id,name,school_id,classroom_id',
                'subject.school:id,name',
                'subject.classroom:id,name,academic_year',
            ])
            ->withCount('questions')
            ->withCount([
                'questions as essay_questions_count' => fn ($query) => $query->where('type', ExamQuestion::TYPE_ESSAY),
                'questions as multiple_choice_questions_count' => fn ($query) => $query->where('type', ExamQuestion::TYPE_MULTIPLE_CHOICE),
            ])
            ->orderBy('title')
            ->get();

        $subjects = $request->user()
            ->subjectsAsTeacher()
            ->whereIn('id', $bankPackages->pluck('subject_id')->unique())
            ->with(['school:id,name', 'classroom:id,name,academic_year'])
            ->orderBy('name')
            ->get();

        $requestedSubjectId = old('subject_id', $request->query('subject_id'));
        $selectedSubjectId = is_numeric($requestedSubjectId)
            && $subjects->contains('id', (int) $requestedSubjectId)
            ? (int) $requestedSubjectId
            : null;

        if ($selectedSubjectId === null && $subjects->count() === 1) {
            $selectedSubjectId = $subjects->first()->id;
        }

        return view('portal.teacher-exam-create', [
            'bankPackages' => $bankPackages,
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer'],
            'bank_package_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'between:1,300'],
            'question_count' => ['required', 'integer', 'between:1,100'],
            'essay_question_count' => ['required', 'integer', 'between:0,100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_published' => ['nullable', 'boolean'],
            'enable_proctoring' => ['nullable', 'boolean'],
            'max_violations' => ['nullable', 'integer', 'between:1,10'],
        ]);

        $bankPackage = Exam::query()
            ->whereKey($validated['bank_package_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('type', Exam::TYPE_QUESTION_BANK)
            ->whereHas('subject', fn ($query) => $query->where('teacher_id', $request->user()->id))
            ->withCount([
                'questions',
                'questions as essay_questions_count' => fn ($query) => $query->where('type', ExamQuestion::TYPE_ESSAY),
                'questions as multiple_choice_questions_count' => fn ($query) => $query->where('type', ExamQuestion::TYPE_MULTIPLE_CHOICE),
            ])
            ->firstOrFail();

        $questionCount = (int) $validated['question_count'];
        $essayQuestionCount = (int) $validated['essay_question_count'];
        $multipleChoiceQuestionCount = $questionCount - $essayQuestionCount;

        if ($essayQuestionCount > $bankPackage->essay_questions_count) {
            return back()->withErrors(['essay_question_count' => 'Paket soal tidak memiliki cukup soal esai untuk jumlah yang dipilih.'])->withInput();
        }

        if ($multipleChoiceQuestionCount < 0 || $multipleChoiceQuestionCount > $bankPackage->multiple_choice_questions_count) {
            return back()->withErrors(['question_count' => 'Jumlah soal pilihan ganda yang diminta melebihi isi paket Bank Soal.'])->withInput();
        }

        $bankPackage->sessionsUsingAsBankPackage()->create([
            'subject_id' => $bankPackage->subject_id,
            'type' => Exam::TYPE_SESSION,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'duration_minutes' => $validated['duration_minutes'],
            'question_count' => $questionCount,
            'essay_question_count' => $essayQuestionCount,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_published' => $request->boolean('is_published'),
            'enable_proctoring' => $request->boolean('enable_proctoring', true),
            'max_violations' => $validated['max_violations'] ?? 3,
        ]);

        return redirect()->route('teacher.exam-sessions')->with('status', 'Sesi ujian berhasil disimpan. Soal akan diacak untuk setiap siswa dengan jumlah esai yang sama.');
    }

    public function togglePublish(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);
        $request->user()->subjectsAsTeacher()->findOrFail($exam->subject_id);
        $isPublished = DB::transaction(function () use ($exam): bool {
            $lockedExam = Exam::query()->lockForUpdate()->findOrFail($exam->id);
            abort_if($lockedExam->isArchived(), 404);

            if ($lockedExam->is_published && $lockedExam->attempts()->whereNull('submitted_at')->exists()) {
                throw ValidationException::withMessages([
                    'exam' => 'Sesi belum dapat dijadikan draf karena masih ada siswa yang sedang mengerjakan.',
                ]);
            }

            if (! $lockedExam->is_published && $lockedExam->bank_package_id !== null) {
                $essayCount = $lockedExam->bankPackage->questions()->where('type', ExamQuestion::TYPE_ESSAY)->count();
                $multipleChoiceCount = $lockedExam->bankPackage->questions()->where('type', ExamQuestion::TYPE_MULTIPLE_CHOICE)->count();
                $requestedMultipleChoiceCount = (int) $lockedExam->question_count - (int) $lockedExam->essay_question_count;

                if ($essayCount < $lockedExam->essay_question_count || $multipleChoiceCount < $requestedMultipleChoiceCount) {
                    throw ValidationException::withMessages([
                        'exam' => 'Paket Bank Soal tidak lagi memiliki jumlah PG dan esai yang cukup untuk sesi ini.',
                    ]);
                }
            }

            $lockedExam->update(['is_published' => ! $lockedExam->is_published]);

            return $lockedExam->is_published;
        });

        $message = $isPublished ? 'Ujian berhasil dipublikasikan.' : 'Ujian berhasil dijadikan draf.';

        return redirect()->route('teacher.exam-sessions')->with('status', $message);
    }

    public function destroy(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);
        $request->user()->subjectsAsTeacher()->findOrFail($exam->subject_id);

        DB::transaction(function () use ($exam): void {
            $lockedExam = Exam::query()->lockForUpdate()->findOrFail($exam->id);
            $attemptsExist = $lockedExam->attempts()->exists();

            if ($lockedExam->is_published || $lockedExam->isArchived() || $attemptsExist) {
                throw ValidationException::withMessages([
                    'exam' => 'Hanya sesi draf yang belum pernah dikerjakan yang dapat dihapus. Arsipkan sesi yang sudah digunakan.',
                ]);
            }

            $lockedExam->delete();
        });

        return redirect()->route('teacher.exam-sessions')->with('status', 'Sesi draf berhasil dihapus.');
    }

    public function archive(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);
        $request->user()->subjectsAsTeacher()->findOrFail($exam->subject_id);

        DB::transaction(function () use ($exam, $request): void {
            $lockedExam = Exam::query()->lockForUpdate()->findOrFail($exam->id);

            if ($lockedExam->isArchived()) {
                return;
            }

            if (! $lockedExam->is_published && ! $lockedExam->attempts()->exists()) {
                throw ValidationException::withMessages([
                    'exam' => 'Sesi draf kosong sebaiknya dihapus. Arsip digunakan untuk sesi yang sudah terbit atau memiliki riwayat.',
                ]);
            }

            if ($lockedExam->attempts()->whereNull('submitted_at')->exists()) {
                throw ValidationException::withMessages([
                    'exam' => 'Sesi belum dapat diarsipkan karena masih ada siswa yang sedang mengerjakan.',
                ]);
            }

            $lockedExam->update([
                'is_published' => false,
                'archived_at' => now(),
                'archived_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('teacher.exam-sessions')->with('status', 'Sesi berhasil diarsipkan. Riwayat pengerjaan tetap tersimpan.');
    }

    public function restore(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);
        $request->user()->subjectsAsTeacher()->findOrFail($exam->subject_id);

        DB::transaction(function () use ($exam): void {
            $lockedExam = Exam::query()->lockForUpdate()->findOrFail($exam->id);

            if (! $lockedExam->isArchived()) {
                throw ValidationException::withMessages(['exam' => 'Sesi ini tidak berada di arsip.']);
            }

            $lockedExam->update([
                'archived_at' => null,
                'archived_by' => null,
                'is_published' => false,
            ]);
        });

        return redirect()->route('teacher.exam-sessions')->with('status', 'Sesi dipulihkan sebagai draf. Periksa jadwal sebelum menerbitkannya kembali.');
    }
}
