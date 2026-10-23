<?php

namespace App\Http\Controllers;

use App\Services\ExamQuestionImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;
use RuntimeException;

class TeacherExamController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = $request->user()->subjectsAsTeacher()->with(['exams' => fn ($query) => $query->withCount('questions')->latest()])->orderBy('name')->get();

        return view('portal.teacher-exams', ['subjects' => $subjects]);
    }

    public function create(Request $request): View
    {
        return view('portal.teacher-exam-create', [
            'subjects' => $request->user()->subjectsAsTeacher()->orderBy('name')->get(),
            'importedQuestions' => session('imported_questions', []),
        ]);
    }

    public function import(Request $request, ExamQuestionImporter $importer): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', File::types(['pdf', 'xlsx'])->max('10mb')],
        ]);

        try {
            $questions = $importer->import($validated['file']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()])->withInput();
        }

        return redirect()->route('teacher.exam-sessions.create')
            ->with('imported_questions', $questions)
            ->with('status', count($questions).' soal terbaca. Tinjau soal dan kunci sebelum menyimpan ujian.');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'duration_minutes' => ['required', 'integer', 'between:1,300'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'is_published' => ['nullable', 'boolean'],
            'questions_json' => ['required', 'string', 'max:200000'],
        ]);
        $subject = $request->user()->subjectsAsTeacher()->findOrFail($validated['subject_id']);

        $questions = json_decode($validated['questions_json'], true);
        if (! is_array($questions) || count($questions) < 1 || count($questions) > 100) {
            return back()->withErrors(['questions_json' => 'Ujian harus memiliki 1 sampai 100 soal.'])->withInput();
        }

        foreach ($questions as $index => $question) {
            $questionValidator = validator($question, [
                'prompt' => ['required', 'string', 'max:5000'],
                'options' => ['required', 'array:A,B,C,D'],
                'options.A' => ['required', 'string', 'max:1000'],
                'options.B' => ['required', 'string', 'max:1000'],
                'options.C' => ['required', 'string', 'max:1000'],
                'options.D' => ['required', 'string', 'max:1000'],
                'answer_key' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
                'points' => ['required', 'integer', 'between:1,100'],
            ]);

            if ($questionValidator->fails()) {
                return back()->withErrors(['questions_json' => 'Periksa kembali soal nomor '.($index + 1).'.'])->withInput();
            }
        }

        DB::transaction(function () use ($validated, $questions, $subject, $request): void {
            $exam = $subject->exams()->create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'duration_minutes' => $validated['duration_minutes'],
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'is_published' => $request->boolean('is_published'),
            ]);

            foreach (array_values($questions) as $index => $question) {
                $exam->questions()->create([...$question, 'position' => $index + 1]);
            }
        });

        return redirect()->route('teacher.exam-sessions')->with('status', 'Ujian berhasil disimpan.');
    }
}
