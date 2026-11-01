<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Services\FoundationReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FoundationSchoolReportController extends Controller
{
    public function directory(Request $request, FoundationReportService $reports): View
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:120']]);
        $search = trim($validated['q'] ?? '');
        $schools = $reports->schoolOverview(null, null);

        if ($search !== '') {
            $schools = $schools->filter(fn (School $school): bool => str_contains(mb_strtolower($school->name.' '.$school->code), mb_strtolower($search)))->values();
        }

        return view('portal.foundation-schools', [
            'schools' => $schools,
            'search' => $search,
            'totalSchools' => School::query()->count(),
        ]);
    }

    public function report(Request $request, FoundationReportService $reports): View
    {
        $filters = $this->validatedFilters($request);
        $schoolRows = $reports->schoolOverview($filters['from'], $filters['to']);

        if ($filters['school_id'] !== null) {
            $schoolRows = $schoolRows->where('id', $filters['school_id'])->values();
        }

        return view('portal.foundation-reports', [
            'filters' => $filters,
            'schools' => $schoolRows,
            'reportSchools' => School::query()->orderBy('name')->get(['id', 'name']),
            'metrics' => $reports->metrics($filters['from'], $filters['to'], $filters['school_id']),
        ]);
    }

    public function show(Request $request, School $school, FoundationReportService $reports): View
    {
        $filters = $this->validatedFilters($request);
        $school->loadCount([
            'users as students_count' => fn ($query) => $query->where('role', User::ROLE_STUDENT),
            'users as teachers_count' => fn ($query) => $query->where('role', User::ROLE_TEACHER),
            'subjects',
        ]);

        return view('portal.foundation-school-report', [
            'school' => $school,
            'filters' => $filters,
            'metrics' => $reports->metrics($filters['from'], $filters['to'], $school->id),
            'classrooms' => $reports->schoolClassrooms($school),
            'exams' => $reports->schoolExams($school, $filters['from'], $filters['to']),
        ]);
    }

    public function export(Request $request, FoundationReportService $reports): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $schoolRows = $reports->schoolOverview($filters['from'], $filters['to']);

        if ($filters['school_id'] !== null) {
            $schoolRows = $schoolRows->where('id', $filters['school_id'])->values();
        }

        return response()->streamDownload(function () use ($schoolRows): void {
            $stream = fopen('php://output', 'w');

            if ($stream === false) {
                return;
            }

            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Sekolah', 'Kode', 'Status', 'Guru', 'Siswa', 'Rombel', 'Mapel', 'Sesi ujian', 'Pengerjaan terkumpul', 'Siswa aktif mengerjakan', 'Rata-rata nilai']);

            foreach ($schoolRows as $school) {
                $examReport = $school->exam_report;
                fputcsv($stream, [
                    $school->name,
                    $school->code,
                    $school->is_active ? 'Aktif' : 'Nonaktif',
                    $school->teachers_count,
                    $school->students_count,
                    $school->classrooms_count,
                    $school->subjects_count,
                    $examReport->exam_sessions_count ?? 0,
                    $examReport->submitted_attempts_count ?? 0,
                    $examReport->participating_students_count ?? 0,
                    $examReport?->average_score !== null ? round((float) $examReport->average_score, 1) : '',
                ]);
            }

            fclose($stream);
        }, 'laporan-yayasan-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * @return array{from: ?string, to: ?string, school_id: ?int}
     */
    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
        ]);

        return [
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'school_id' => isset($validated['school_id']) ? (int) $validated['school_id'] : null,
        ];
    }
}
