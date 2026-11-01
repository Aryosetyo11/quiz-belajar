<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminSchoolController extends Controller
{
    public function index(): View
    {
        $schools = School::query()
            ->withCount(['users as student_count' => fn (Builder $query) => $query->where('role', User::ROLE_STUDENT)])
            ->with(['classrooms' => fn ($query) => $query->withCount(['students', 'subjects'])->orderBy('academic_year')->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('portal.admin-schools', ['schools' => $schools]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'code' => ['required', 'string', 'max:40', 'alpha_dash', 'unique:schools,code'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        School::query()->create($validated);

        return to_route('admin.schools')->with('status', 'Sekolah berhasil ditambahkan.');
    }

    public function storeClassroom(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'academic_year' => ['required', 'string', 'max:20'],
            'level' => ['nullable', 'string', 'max:40'],
        ]);

        $duplicateExists = $school->classrooms()
            ->where('name', $validated['name'])
            ->where('academic_year', $validated['academic_year'])
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['name' => 'Nama rombel sudah digunakan pada tahun ajaran tersebut.'])->withInput();
        }

        $school->classrooms()->create($validated);

        return to_route('admin.schools')->with('status', 'Rombel berhasil dibuat.');
    }

    public function updateClassroom(Request $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'level' => ['nullable', 'string', 'max:40'],
        ]);

        $duplicateExists = Classroom::query()
            ->where('school_id', $classroom->school_id)
            ->where('academic_year', $classroom->academic_year)
            ->where('name', $validated['name'])
            ->where('id', '<>', $classroom->id)
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['name' => 'Nama rombel sudah digunakan pada tahun ajaran tersebut.'])->withInput();
        }

        $classroom->update($validated);

        return to_route('admin.schools')->with('status', 'Data rombel berhasil diperbarui.');
    }

    public function students(Request $request, Classroom $classroom): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $classroom->load('school');

        $roster = $classroom->students()
            ->select('users.id', 'users.name', 'users.nisn')
            ->orderBy('users.name')
            ->paginate(15, ['*'], 'roster_page');

        $availableStudents = User::query()
            ->where('school_id', $classroom->school_id)
            ->where('role', User::ROLE_STUDENT)
            ->whereDoesntHave('classrooms', fn (Builder $query) => $query->whereKey($classroom->id))
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $query->where(fn (Builder $query) => $query->where('name', 'like', "%{$term}%")
                    ->orWhere('nisn', 'like', "%{$term}%"));
            })
            ->orderBy('name')
            ->paginate(10, ['id', 'name', 'nisn'], 'candidates_page')
            ->withQueryString();

        return view('portal.admin-class-students', [
            'classroom' => $classroom,
            'roster' => $roster,
            'availableStudents' => $availableStudents,
        ]);
    }

    public function enrollStudent(Request $request, Classroom $classroom): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_STUDENT)->where('school_id', $classroom->school_id)],
        ]);

        $student = User::query()->where('role', User::ROLE_STUDENT)->findOrFail($validated['student_id']);

        $alreadyAssignedToDifferentClass = $student->classrooms()
            ->where('classrooms.school_id', $classroom->school_id)
            ->where('classrooms.academic_year', $classroom->academic_year)
            ->where('classrooms.id', '<>', $classroom->id)
            ->exists();

        if ($alreadyAssignedToDifferentClass) {
            return to_route('admin.classrooms.students', $classroom)
                ->withErrors(['student_id' => 'Siswa sudah terdaftar di rombel lain pada tahun ajaran ini. Keluarkan dari rombel sebelumnya terlebih dahulu.']);
        }

        DB::transaction(function () use ($classroom, $student): void {
            $classroom->students()->syncWithoutDetaching([$student->id]);

            foreach ($classroom->subjects()->get() as $subject) {
                $subject->students()->syncWithoutDetaching([$student->id]);
            }
        });

        return to_route('admin.classrooms.students', $classroom)
            ->with('status', $student->name.' berhasil didaftarkan ke rombel '.$classroom->name.'.');
    }

    public function removeStudent(Classroom $classroom, User $student): RedirectResponse
    {
        abort_unless($student->role === User::ROLE_STUDENT && (int) $student->school_id === (int) $classroom->school_id, 404);

        DB::transaction(function () use ($classroom, $student): void {
            $classroom->students()->detach($student->id);

            foreach ($classroom->subjects()->get() as $subject) {
                $subject->students()->detach($student->id);
            }
        });

        return to_route('admin.classrooms.students', $classroom)
            ->with('status', $student->name.' sudah dikeluarkan dari rombel '.$classroom->name.'.');
    }
}
