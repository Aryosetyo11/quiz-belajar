<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Subject;
use App\Models\User;
use App\Services\ExamQuestionImporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherQuestionBankController extends Controller
{
    public function showPackage(Request $request, Exam $exam): View
    {
        $exam = Exam::query()
            ->whereKey($exam->id)
            ->whereIn('id', $this->visiblePackages($request)->select('id'))
            ->with('subject:id,name,school_id,classroom_id')
            ->with('subject.school:id,name', 'subject.classroom:id,name')
            ->withCount('attempts')
            ->firstOrFail();

        $questions = $exam->questions()->get();
        $isPackageLocked = $exam->is_published
            || $exam->attempts_count > 0
            || $exam->sessionsUsingAsBankPackage()->where(function (Builder $query): void {
                $query->where('is_published', true)->orWhereHas('attempts');
            })->exists();
        $canDeletePackage = ! $exam->is_published
            && (int) $exam->attempts_count === 0
            && ! $exam->sessionsUsingAsBankPackage()->exists();

        return view('portal.teacher-question-bank-package', [
            'package' => $exam,
            'questions' => $questions,
            'isPackageLocked' => $isPackageLocked,
            'canDeletePackage' => $canDeletePackage,
            'questionBankRoutePrefix' => $this->routePrefix($request),
        ]);
    }

    public function updatePackage(Request $request, Exam $exam): RedirectResponse
    {
        $package = $this->visiblePackages($request)->whereKey($exam->id)->firstOrFail();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
        ]);

        $package->update(['title' => trim($validated['title'])]);

        return redirect()->route($this->routePrefix($request).'.package', $package)
            ->with('status', 'Nama paket berhasil diperbarui.');
    }

    public function destroyPackage(Request $request, Exam $exam): RedirectResponse
    {
        $package = $this->visiblePackages($request)->whereKey($exam->id)->firstOrFail();
        $wasDeleted = DB::transaction(function () use ($request, $package): bool {
            $lockedPackage = $this->visiblePackages($request)
                ->whereKey($package->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPackage->is_published
                || $lockedPackage->attempts()->exists()
                || $lockedPackage->sessionsUsingAsBankPackage()->exists()) {
                return false;
            }

            $lockedPackage->delete();

            return true;
        });

        if (! $wasDeleted) {
            return back()->withErrors([
                'package' => 'Paket tidak dapat dihapus karena sudah dipakai atau terhubung ke sesi ujian. Hapus sesi ujian terkait terlebih dahulu.',
            ]);
        }

        return redirect()->route($this->routePrefix($request))
            ->with('status', 'Paket bank soal beserta semua soalnya berhasil dihapus.');
    }

    public function storePackageQuestion(Request $request, Exam $exam): RedirectResponse
    {
        $exam = $this->editablePackage($request, $exam);
        $validated = $this->validatePackageQuestion($request);

        $exam->questions()->create([
            ...$validated,
            'position' => (int) $exam->questions()->max('position') + 1,
        ]);

        return redirect()->route($this->routePrefix($request).'.package', $exam)
            ->with('status', 'Soal berhasil ditambahkan ke paket.');
    }

    public function updatePackageQuestion(Request $request, Exam $exam, ExamQuestion $question): RedirectResponse
    {
        $exam = $this->editablePackage($request, $exam);
        $question = $exam->questions()->findOrFail($question->id);
        $question->update($this->validatePackageQuestion($request));

        return redirect()->route($this->routePrefix($request).'.package', $exam)
            ->with('status', 'Soal berhasil diperbarui.');
    }

    /**
     * @return array{type: string, prompt: string, options: array<string, string>|null, answer_key: string|null, sample_answer: string|null, points: int}
     */
    private function validatePackageQuestion(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', 'in:multiple_choice,essay'],
            'prompt' => ['required', 'string', 'max:5000'],
            'options' => ['nullable', 'array:A,B,C,D'],
            'options.A' => ['required_if:type,multiple_choice', 'nullable', 'string', 'max:1000'],
            'options.B' => ['required_if:type,multiple_choice', 'nullable', 'string', 'max:1000'],
            'options.C' => ['required_if:type,multiple_choice', 'nullable', 'string', 'max:1000'],
            'options.D' => ['required_if:type,multiple_choice', 'nullable', 'string', 'max:1000'],
            'answer_key' => ['required_if:type,multiple_choice', 'nullable', 'in:A,B,C,D'],
            'sample_answer' => ['nullable', 'string', 'max:2000'],
            'points' => ['required', 'integer', 'between:1,100'],
        ]);

        $isEssay = $validated['type'] === ExamQuestion::TYPE_ESSAY;

        return [
            'type' => $validated['type'],
            'prompt' => $validated['prompt'],
            'options' => $isEssay ? null : $validated['options'],
            'answer_key' => $isEssay ? null : $validated['answer_key'],
            'sample_answer' => $isEssay ? ($validated['sample_answer'] ?? null) : null,
            'points' => (int) $validated['points'],
        ];
    }

    private function editablePackage(Request $request, Exam $exam): Exam
    {
        $exam = Exam::query()
            ->whereKey($exam->id)
            ->whereIn('id', $this->visiblePackages($request)->select('id'))
            ->withCount('attempts')
            ->firstOrFail();

        abort_if(
            $exam->is_published
                || $exam->attempts_count > 0
                || $exam->sessionsUsingAsBankPackage()->where(function ($query): void {
                    $query->where('is_published', true)->orWhereHas('attempts');
                })->exists(),
            403,
            'Paket terkunci setelah sesi ujian dipublikasikan atau mulai dikerjakan.',
        );

        return $exam;
    }

    public function index(Request $request): View
    {
        $subjects = $this->availableSubjects($request)
            ->orderBy('name')
            ->get(['id', 'name', 'school_id', 'classroom_id']);

        $selectedSubjectId = $request->input('subject_id');
        $selectedType = $request->input('type', 'all');
        $search = trim((string) $request->input('search'));

        $visiblePackageIds = $this->visiblePackages($request)->select('id');
        $questionsQuery = ExamQuestion::query()
            ->whereIn('exam_id', $visiblePackageIds)
            ->with(['exam' => fn ($query) => $query->select('id', 'title', 'subject_id')->withCount('attempts')->with('subject:id,name,school_id,classroom_id')->with('subject.school:id,name', 'subject.classroom:id,name')]);

        if ($selectedSubjectId) {
            $questionsQuery->whereHas('exam', fn ($q) => $q->where('subject_id', $selectedSubjectId));
        }

        if (in_array($selectedType, [ExamQuestion::TYPE_MULTIPLE_CHOICE, ExamQuestion::TYPE_ESSAY], true)) {
            $questionsQuery->where('type', $selectedType);
        }

        if ($search !== '') {
            $questionsQuery->where(function ($q) use ($search): void {
                $q->where('prompt', 'like', "%{$search}%")
                    ->orWhere('options', 'like', "%{$search}%")
                    ->orWhere('sample_answer', 'like', "%{$search}%");
            });
        }

        $totalQuestionsCount = ExamQuestion::query()
            ->whereIn('exam_id', $visiblePackageIds)
            ->count();

        $multipleChoiceCount = ExamQuestion::query()
            ->whereIn('exam_id', $visiblePackageIds)
            ->where('type', ExamQuestion::TYPE_MULTIPLE_CHOICE)
            ->count();

        $essayCount = ExamQuestion::query()
            ->whereIn('exam_id', $visiblePackageIds)
            ->where('type', ExamQuestion::TYPE_ESSAY)
            ->count();

        $questions = $questionsQuery
            ->latest('id')
            ->get();

        return view('portal.teacher-question-bank', [
            'questions' => $questions,
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedType' => $selectedType,
            'search' => $search,
            'totalQuestionsCount' => $totalQuestionsCount,
            'multipleChoiceCount' => $multipleChoiceCount,
            'essayCount' => $essayCount,
            'questionBankRoutePrefix' => $this->routePrefix($request),
            'isGlobalQuestionBankManager' => $this->isGlobalManager($request),
        ]);
    }

    public function create(Request $request): View
    {
        $subjects = $this->availableSubjects($request)
            ->orderBy('name')
            ->get(['id', 'name', 'school_id', 'classroom_id']);

        return view('portal.teacher-question-bank-create', [
            'subjects' => $subjects,
            'importedQuestions' => session('imported_questions', []),
            'questionBankRoutePrefix' => $this->routePrefix($request),
            'isGlobalQuestionBankManager' => $this->isGlobalManager($request),
        ]);
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'questions_json' => ['required', 'string'],
        ]);

        $subject = $this->availableSubjects($request)->findOrFail($validated['subject_id']);

        $decoded = json_decode($validated['questions_json'], true);
        if (! is_array($decoded) || count($decoded) === 0 || count($decoded) > 100) {
            return back()->withErrors(['questions_json' => 'Minimal 1 soal dan maksimal 100 soal.'])->withInput();
        }

        foreach ($decoded as $index => $item) {
            $num = $index + 1;
            $type = $item['type'] ?? ExamQuestion::TYPE_MULTIPLE_CHOICE;
            $prompt = trim((string) ($item['prompt'] ?? ''));

            if ($prompt === '') {
                return back()->withErrors(['questions_json' => "Pertanyaan pada butir soal #{$num} wajib diisi."])->withInput();
            }

            if ($type === ExamQuestion::TYPE_MULTIPLE_CHOICE) {
                $options = $item['options'] ?? [];
                if (empty($options['A']) || empty($options['B'])) {
                    return back()->withErrors(['questions_json' => "Pilihan A dan B pada butir soal #{$num} wajib diisi."])->withInput();
                }
            }
        }

        DB::transaction(function () use ($validated, $decoded, $subject): void {
            $exam = $subject->exams()->create([
                'type' => Exam::TYPE_QUESTION_BANK,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? 'Paket soal disusun secara manual ke Bank Soal.',
                'duration_minutes' => 60,
                'is_published' => false,
                'enable_proctoring' => true,
                'max_violations' => 3,
            ]);

            foreach ($decoded as $index => $item) {
                $type = $item['type'] ?? ExamQuestion::TYPE_MULTIPLE_CHOICE;
                $points = max(1, min(100, (int) ($item['points'] ?? 10)));

                if ($type === ExamQuestion::TYPE_ESSAY) {
                    $exam->questions()->create([
                        'type' => ExamQuestion::TYPE_ESSAY,
                        'prompt' => trim((string) $item['prompt']),
                        'options' => null,
                        'answer_key' => null,
                        'sample_answer' => ! empty($item['sample_answer']) ? trim((string) $item['sample_answer']) : null,
                        'points' => $points,
                        'position' => $index + 1,
                    ]);
                } else {
                    $options = array_map(fn ($opt) => trim((string) $opt), (array) ($item['options'] ?? []));
                    $exam->questions()->create([
                        'type' => ExamQuestion::TYPE_MULTIPLE_CHOICE,
                        'prompt' => trim((string) $item['prompt']),
                        'options' => $options,
                        'answer_key' => $item['answer_key'] ?? 'A',
                        'sample_answer' => null,
                        'points' => $points,
                        'position' => $index + 1,
                    ]);
                }
            }
        });

        return redirect()->route($this->routePrefix($request))
            ->with('status', 'Berhasil menyimpan paket soal dengan '.count($decoded).' butir ke dalam Bank Soal!');
    }

    public function import(Request $request, ExamQuestionImporter $importer): RedirectResponse
    {
        $validated = $request->validate([
            'subject_id' => ['required_without:exam_id', 'nullable', 'integer'],
            'title' => ['required_without:exam_id', 'nullable', 'string', 'max:160'],
            'exam_id' => ['nullable', 'integer'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $subject = null;
        if (! empty($validated['exam_id'])) {
            $exam = $this->editablePackage($request, Exam::query()->findOrFail($validated['exam_id']));
            abort_unless((int) $exam->subject_id === (int) $validated['subject_id'], 422);
        } else {
            $subject = $this->availableSubjects($request)->findOrFail($validated['subject_id']);
            $exam = null;
        }

        $extension = strtolower((string) $validated['file']->getClientOriginalExtension());
        if (! in_array($extension, ['xlsx', 'csv', 'txt', 'pdf'], true)) {
            return back()->withErrors(['file' => 'Format file tidak didukung. Harap unggah file .xlsx, .csv, .txt, atau .pdf.'])->withInput();
        }

        try {
            $questions = $importer->import($validated['file']);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()])->withInput();
        }

        DB::transaction(function () use ($validated, $questions, $subject, &$exam): void {
            if ($exam === null) {
                $exam = $subject->exams()->create([
                    'type' => Exam::TYPE_QUESTION_BANK,
                    'title' => $validated['title'],
                    'description' => 'Paket soal diimpor ke Bank Soal.',
                    'duration_minutes' => 60,
                    'is_published' => false,
                    'enable_proctoring' => true,
                    'max_violations' => 3,
                ]);
            }

            $position = (int) $exam->questions()->max('position');

            foreach (array_values($questions) as $index => $q) {
                $exam->questions()->create([
                    'type' => $q['type'] ?? ExamQuestion::TYPE_MULTIPLE_CHOICE,
                    'prompt' => $q['prompt'],
                    'options' => $q['options'] ?? null,
                    'answer_key' => $q['answer_key'] ?? null,
                    'sample_answer' => $q['sample_answer'] ?? null,
                    'points' => $q['points'] ?? 10,
                    'position' => $position + $index + 1,
                ]);
            }
        });

        return redirect()->route($this->routePrefix($request).'.package', $exam)
            ->with('status', 'Berhasil mengimpor '.count($questions).' butir soal ke dalam database Bank Soal!');
    }

    public function destroy(Request $request, ExamQuestion $question): RedirectResponse
    {
        $question->load('exam.subject');
        $exam = $question->exam;
        abort_unless($this->visiblePackages($request)->whereKey($question->exam_id)->exists(), 404);

        if ($exam->is_published || $exam->attempts()->exists() || $exam->sessionsUsingAsBankPackage()->where(function ($query): void {
            $query->where('is_published', true)->orWhereHas('attempts');
        })->exists()) {
            return redirect()->route($this->routePrefix($request))
                ->withErrors(['question' => 'Soal dari ujian yang sudah dipublikasikan atau mulai dikerjakan tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($question, $exam): void {
            $position = $question->position;
            $question->delete();

            $exam->questions()
                ->where('position', '>', $position)
                ->decrement('position');
        });

        return redirect()->route($this->routePrefix($request).'.package', $exam)
            ->with('status', 'Soal berhasil dihapus.');
    }

    private function isGlobalManager(Request $request): bool
    {
        return $request->user()->role === User::ROLE_ADMIN_REMOTE;
    }

    private function routePrefix(Request $request): string
    {
        return $this->isGlobalManager($request) ? 'admin.question-bank' : 'teacher.question-bank';
    }

    private function availableSubjects(Request $request): Builder
    {
        $query = Subject::query();

        if (! $this->isGlobalManager($request)) {
            $query->where('teacher_id', $request->user()->id);
        }

        return $query->with(['school:id,name', 'classroom:id,name']);
    }

    private function visiblePackages(Request $request): Builder
    {
        $query = Exam::query()->where('type', Exam::TYPE_QUESTION_BANK);

        if (! $this->isGlobalManager($request)) {
            $query->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('teacher_id', $request->user()->id));
        }

        return $query;
    }

    /**
     * Unduh Template Excel / CSV Soal Pilihan Ganda siap isi.
     */
    public function downloadMultipleChoiceTemplate(): StreamedResponse
    {
        $filename = 'template-soal-pilihan-ganda.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM agar Excel di Windows membaca karakter spesial dan tanda petik dengan sempurna
            fwrite($handle, "\xEF\xBB\xBF");

            // Header kolom sesuai format yang dibaca ExamQuestionImporter
            fputcsv($handle, ['Pertanyaan', 'A', 'B', 'C', 'D', 'Kunci', 'Poin']);

            // Baris contoh soal 1
            fputcsv($handle, [
                'Berapakah hasil dari 25 x 4 + 15?',
                '115',
                '100',
                '125',
                '105',
                'A',
                '10',
            ]);

            // Baris contoh soal 2
            fputcsv($handle, [
                'Organel sel yang berfungsi sebagai tempat respirasi seluler dan penghasil energi adalah...',
                'Ribosom',
                'Mitokondria',
                'Kloroplas',
                'Badan Golgi',
                'B',
                '10',
            ]);

            // Baris contoh soal 3
            fputcsv($handle, [
                'Siapakah tokoh yang membacakan teks Proklamasi Kemerdekaan Republik Indonesia?',
                'Mohammad Hatta',
                'Soepomo',
                'Ir. Soekarno',
                'Sutan Sjahrir',
                'C',
                '10',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Unduh Template Excel / CSV Soal Esai siap isi.
     */
    public function downloadEssayTemplate(): StreamedResponse
    {
        $filename = 'template-soal-esai-uraian.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Pertanyaan', 'Pedoman_Jawaban', 'Poin']);

            fputcsv($handle, [
                'Jelaskan proses fotosintesis pada tumbuhan hijau dan sebutkan zat yang dihasilkan!',
                'Fotosintesis terjadi di kloroplas daun dengan bantuan cahaya matahari, air, dan karbon dioksida. Menghasilkan glukosa dan oksigen.',
                '20',
            ]);

            fputcsv($handle, [
                'Tuliskan isi dari Pembukaan UUD 1945 alinea pertama!',
                'Bahwa sesungguhnya kemerdekaan itu ialah hak segala bangsa dan oleh sebab itu, maka penjajahan di atas dunia harus dihapuskan...',
                '20',
            ]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadCombinedTemplate(): StreamedResponse
    {
        $filename = 'template-soal-pg-dan-esai.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Tipe', 'Pertanyaan', 'A', 'B', 'C', 'D', 'Kunci', 'Poin', 'Pedoman_Jawaban']);
            fputcsv($handle, ['multiple_choice', 'Ibu kota Indonesia adalah...', 'Jakarta', 'Bandung', 'Surabaya', 'Medan', 'A', '10', '']);
            fputcsv($handle, ['essay', 'Jelaskan secara singkat proses fotosintesis.', '', '', '', '', '', '20', 'Fotosintesis mengubah air dan karbon dioksida menjadi glukosa dan oksigen dengan bantuan cahaya.']);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /** Unduh template PDF yang dapat digunakan sebagai contoh impor. */
    public function downloadPdfExample(): BinaryFileResponse
    {
        $filePath = public_path('templates/template-impor-bank-soal.pdf');
        abort_unless(is_file($filePath), 404);

        return response()->download($filePath, 'template-impor-bank-soal.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
