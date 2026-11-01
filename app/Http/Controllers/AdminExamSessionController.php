<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\School;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminExamSessionController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            'status' => ['nullable', 'in:all,published,draft,archived'],
        ]);
        $status = $validated['status'] ?? 'all';

        $sessions = Exam::query()
            ->where('type', Exam::TYPE_SESSION)
            ->with([
                'subject:id,name,teacher_id,school_id,classroom_id',
                'subject.teacher:id,name',
                'subject.school:id,name',
                'subject.classroom:id,name,academic_year',
                'archivedBy:id,name',
            ])
            ->withCount('attempts')
            ->withCount(['attempts as active_attempts_count' => fn ($query) => $query->whereNull('submitted_at')])
            ->when(isset($validated['school_id']), fn (Builder $query) => $query->whereHas('subject', fn (Builder $subjectQuery) => $subjectQuery->where('school_id', $validated['school_id'])))
            ->when($status === 'archived', fn (Builder $query) => $query->whereNotNull('archived_at'))
            ->when($status === 'published', fn (Builder $query) => $query->whereNull('archived_at')->where('is_published', true))
            ->when($status === 'draft', fn (Builder $query) => $query->whereNull('archived_at')->where('is_published', false))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('portal.admin-exams', [
            'sessions' => $sessions,
            'schools' => School::query()->orderBy('name')->get(['id', 'name']),
            'filters' => [
                'school_id' => isset($validated['school_id']) ? (int) $validated['school_id'] : null,
                'status' => $status,
            ],
        ]);
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);

        DB::transaction(function () use ($exam): void {
            $lockedExam = Exam::query()->lockForUpdate()->findOrFail($exam->id);

            if ($lockedExam->is_published || $lockedExam->isArchived() || $lockedExam->attempts()->exists()) {
                throw ValidationException::withMessages([
                    'exam' => 'Hanya sesi draf yang belum pernah dikerjakan yang dapat dihapus. Arsipkan sesi yang sudah digunakan.',
                ]);
            }

            $lockedExam->delete();
        });

        return redirect()->route('admin.exam-sessions')->with('status', 'Sesi draf berhasil dihapus.');
    }

    public function archive(Request $request, Exam $exam): RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);

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

        return redirect()->route('admin.exam-sessions')->with('status', 'Sesi berhasil diarsipkan. Riwayat pengerjaan tetap tersimpan.');
    }

    public function restore(Exam $exam): RedirectResponse
    {
        abort_unless($exam->type === Exam::TYPE_SESSION, 404);

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

        return redirect()->route('admin.exam-sessions')->with('status', 'Sesi dipulihkan sebagai draf.');
    }
}
