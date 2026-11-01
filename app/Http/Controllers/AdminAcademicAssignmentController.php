<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\School;
use App\Models\Subject;
use App\Models\SubjectSchedule;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminAcademicAssignmentController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()
            ->with(['teacher:id,name,email,school_id', 'school:id,name', 'classroom:id,name,academic_year', 'schedules'])
            ->withCount([
                'students',
                'materials',
                'exams as sessions_count' => fn (Builder $query) => $query->where('type', 'session'),
            ])
            ->orderBy('name')
            ->get();
        $teachers = User::query()
            ->where('role', User::ROLE_TEACHER)
            ->with('school:id,name')
            ->withCount('subjectsAsTeacher')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
        $schools = School::query()->where('is_active', true)->with('classrooms:id,school_id,name,academic_year')->orderBy('name')->get();

        return view('portal.admin-assignments', [
            'subjects' => $subjects,
            'teachers' => $teachers,
            'schools' => $schools,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_TEACHER)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $teacher = User::query()->where('role', User::ROLE_TEACHER)->findOrFail($validated['teacher_id']);
        $classroom = Classroom::query()->findOrFail($validated['classroom_id']);
        abort_unless((int) $classroom->school_id === (int) $validated['school_id'] && (int) $teacher->school_id === (int) $validated['school_id'], 422, 'Sekolah, rombel, dan pengajar harus berada di sekolah yang sama.');
        $duplicateExists = Subject::query()
            ->where('teacher_id', $teacher->id)
            ->where('classroom_id', $classroom->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($validated['name'])])
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['name' => 'Guru ini sudah memiliki mapel dengan nama tersebut di rombel ini.'])->withInput();
        }

        DB::transaction(function () use ($teacher, $classroom, $validated): void {
            $subject = $teacher->subjectsAsTeacher()->create([
                'school_id' => $classroom->school_id,
                'classroom_id' => $classroom->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            $subject->students()->sync($classroom->students()->pluck('users.id'));
        });

        return to_route('admin.assignments')->with('status', 'Mata pelajaran berhasil dibuat dan ditugaskan kepada guru.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'teacher_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_TEACHER)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $teacher = User::query()->where('role', User::ROLE_TEACHER)->findOrFail($validated['teacher_id']);
        $classroom = Classroom::query()->findOrFail($validated['classroom_id']);
        abort_unless((int) $classroom->school_id === (int) $validated['school_id'] && (int) $teacher->school_id === (int) $validated['school_id'], 422, 'Sekolah, rombel, dan pengajar harus berada di sekolah yang sama.');

        if (((int) $subject->school_id !== (int) $classroom->school_id || (int) $subject->classroom_id !== (int) $classroom->id) && $subject->exams()->exists()) {
            throw ValidationException::withMessages(['classroom_id' => 'Sekolah atau rombel tidak dapat dipindahkan setelah mapel memiliki bank soal atau sesi ujian.']);
        }
        $duplicateExists = Subject::query()
            ->where('teacher_id', $teacher->id)
            ->where('classroom_id', $classroom->id)
            ->where('id', '<>', $subject->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($validated['name'])])
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['name' => 'Guru ini sudah memiliki mapel dengan nama tersebut di rombel ini.'])->withInput();
        }

        DB::transaction(function () use ($subject, $teacher, $classroom, $validated): void {
            $subject->forceFill([
                'teacher_id' => $teacher->id,
                'school_id' => $classroom->school_id,
                'classroom_id' => $classroom->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ])->save();

            $subject->students()->sync($classroom->students()->pluck('users.id'));
        });

        return to_route('admin.assignments')->with('status', 'Penugasan pengajar dan data mata pelajaran berhasil diperbarui.');
    }

    public function storeSchedule(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'location' => ['nullable', 'string', 'max:120'],
        ]);

        $hasConflict = SubjectSchedule::query()
            ->where('day_of_week', $validated['day_of_week'])
            ->where('starts_at', '<', $validated['ends_at'])
            ->where('ends_at', '>', $validated['starts_at'])
            ->whereHas('subject', fn (Builder $query) => $query
                ->where('id', '<>', $subject->id)
                ->where(fn (Builder $query) => $query
                    ->where('teacher_id', $subject->teacher_id)
                    ->orWhere('classroom_id', $subject->classroom_id)))
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'starts_at' => 'Jadwal bertabrakan dengan mapel lain yang diajar guru atau rombel yang sama.',
            ]);
        }

        $subject->schedules()->create($validated);

        return to_route('admin.assignments')->with('status', 'Jadwal mapel berhasil ditambahkan.');
    }

    public function destroySchedule(Subject $subject, SubjectSchedule $schedule): RedirectResponse
    {
        $subject->schedules()->findOrFail($schedule->id)->delete();

        return to_route('admin.assignments')->with('status', 'Jadwal mapel berhasil dihapus.');
    }
}
