<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherSubjectController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = $request->user()
            ->subjectsAsTeacher()
            ->with('classroom:id,name,academic_year')
            ->withCount([
                'students',
                'materials',
                'materials as documents_count' => fn ($query) => $query->where('type', 'file'),
                'materials as videos_count' => fn ($query) => $query->where('type', 'video'),
                'materials as links_count' => fn ($query) => $query->where('type', 'link'),
                'exams as exams_count' => fn ($query) => $query->where('type', Exam::TYPE_SESSION),
            ])
            ->orderBy('name')
            ->get();
        $classrooms = $request->user()->school?->classrooms()
            ->orderBy('academic_year')
            ->orderBy('name')
            ->get() ?? collect();

        return view('portal.teacher-subjects', [
            'subjects' => $subjects,
            'classrooms' => $classrooms,
        ]);
    }

    public function show(Request $request, Subject $subject): View
    {
        $subject = $this->ownedSubject($request, $subject);

        $subject->load([
            'students' => fn ($query) => $query->select('users.id', 'users.name', 'users.nisn', 'users.email')
                ->withPivot('created_at')
                ->orderBy('users.name'),
            'materials' => fn ($query) => $query->latest(),
            'classroom',
            'schedules',
            'exams' => fn ($query) => $query->where('type', Exam::TYPE_SESSION)->withCount(['questions', 'attempts'])->latest(),
        ]);

        return view('portal.teacher-subject-detail', [
            'subject' => $subject,
        ]);
    }

    public function availableStudents(Request $request, Subject $subject): JsonResponse
    {
        $subject = $this->ownedSubject($request, $subject);

        $enrolledIds = $subject->students()->pluck('users.id');

        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->when($request->user()->school_id, fn ($query, int $schoolId) => $query->where('school_id', $schoolId))
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get(['id', 'name', 'nisn']);

        return response()->json($students);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $classroom = Classroom::query()
            ->whereKey($validated['classroom_id'])
            ->where('school_id', $request->user()->school_id)
            ->firstOrFail();

        $duplicateExists = $request->user()->subjectsAsTeacher()
            ->where('classroom_id', $classroom->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($validated['name'])])
            ->exists();

        if ($duplicateExists) {
            return back()->withErrors(['name' => 'Mapel dengan nama tersebut sudah ditugaskan pada rombel ini.'])->withInput();
        }

        $subject = $request->user()->subjectsAsTeacher()->create([
            'school_id' => $classroom->school_id,
            'classroom_id' => $classroom->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);
        $subject->students()->sync($classroom->students()->pluck('users.id'));

        return redirect()->route('teacher.subjects.show', $subject)
            ->with('status', 'Mata pelajaran "'.$subject->name.'" berhasil dibuat. Selamat datang di ruang kelas baru Anda!');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject = $this->ownedSubject($request, $subject);

        $name = $request->input("subjects.{$subject->id}.name") ?? $request->input('name');
        $description = $request->input("subjects.{$subject->id}.description") ?? $request->input('description');

        $validated = validator([
            'name' => $name,
            'description' => $description,
        ], [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $subject->update($validated);

        return redirect()->route('teacher.subjects.show', ['subject' => $subject, 'tab' => 'settings'])
            ->with('status', 'Informasi mata pelajaran berhasil diperbarui.');
    }

    public function enrollStudent(Request $request, Subject $subject): RedirectResponse
    {
        $subject = $this->ownedSubject($request, $subject);

        $studentId = $request->input('student_id');
        $nisn = $request->input('nisn') ?? $request->input("students.{$subject->id}.nisn");

        if ($studentId) {
            $student = User::query()
                ->where('role', User::ROLE_STUDENT)
                ->where('id', $studentId)
                ->first();

            if (! $student) {
                return back()->withErrors(['student_id' => 'Akun siswa tidak ditemukan.'])->withInput();
            }
        } elseif ($nisn) {
            $cleanNisn = trim((string) $nisn);
            if (! preg_match('/^\d{10}$/', $cleanNisn)) {
                $errorKey = $request->has('students') ? "students.{$subject->id}.nisn" : 'nisn';

                return back()->withErrors([$errorKey => 'NISN harus terdiri dari 10 digit angka.'])->withInput();
            }

            $student = User::query()
                ->where('role', User::ROLE_STUDENT)
                ->where('nisn', $cleanNisn)
                ->first();

            if (! $student) {
                $errorKey = $request->has('students') ? "students.{$subject->id}.nisn" : 'nisn';

                return back()->withErrors([$errorKey => 'Akun siswa dengan NISN '.$cleanNisn.' tidak ditemukan.'])->withInput();
            }
        } else {
            return back()->withErrors(['nisn' => 'Pilih siswa dari daftar atau masukkan 10 digit NISN.'])->withInput();
        }

        if ($subject->school_id !== null && (int) $student->school_id !== (int) $subject->school_id) {
            return back()->withErrors(['student_id' => 'Siswa hanya dapat dimasukkan ke rombel dari sekolah yang sama.'])->withInput();
        }

        if ($subject->classroom_id !== null) {
            DB::transaction(function () use ($subject, $student): void {
                $classroom = $subject->classroom()->firstOrFail();
                $classroom->students()->syncWithoutDetaching([$student->id]);

                foreach ($classroom->subjects()->get() as $classSubject) {
                    $classSubject->students()->syncWithoutDetaching([$student->id]);
                }
            });

            return redirect()->route('teacher.subjects.show', ['subject' => $subject, 'tab' => 'students'])
                ->with('status', $student->name.' berhasil didaftarkan ke rombel '.$subject->classroom->name.'.');
        }

        $syncResult = $subject->students()->syncWithoutDetaching([$student->id]);

        if ($syncResult['attached'] === []) {
            $errorKey = $request->has('students') ? "students.{$subject->id}.nisn" : 'student_id';

            return back()->withErrors([$errorKey => 'Siswa '.$student->name.' sudah terdaftar di mata pelajaran ini.'])->withInput();
        }

        return redirect()->route('teacher.subjects.show', ['subject' => $subject, 'tab' => 'students'])
            ->with('status', $student->name.' (NISN: '.$student->nisn.') berhasil didaftarkan ke kelas.');
    }

    public function removeStudent(Request $request, Subject $subject, User $student): RedirectResponse
    {
        $subject = $this->ownedSubject($request, $subject);
        abort_unless($student->role === User::ROLE_STUDENT, 404);

        if ($subject->classroom_id !== null) {
            DB::transaction(function () use ($subject, $student): void {
                $classroom = $subject->classroom()->firstOrFail();
                $classroom->students()->detach($student->id);

                foreach ($classroom->subjects()->get() as $classSubject) {
                    $classSubject->students()->detach($student->id);
                }
            });
        } else {
            $subject->students()->detach($student->id);
        }

        return redirect()->route('teacher.subjects.show', ['subject' => $subject, 'tab' => 'students'])
            ->with('status', 'Siswa '.$student->name.' berhasil dikeluarkan dari mata pelajaran.');
    }

    public function destroy(Request $request, Subject $subject): RedirectResponse
    {
        $subject = $this->ownedSubject($request, $subject);

        $name = $subject->name;
        $subject->delete();

        return redirect()->route('teacher.subjects')
            ->with('status', 'Mata pelajaran "'.$name.'" berhasil dihapus.');
    }

    private function ownedSubject(Request $request, Subject $subject): Subject
    {
        return $request->user()->subjectsAsTeacher()->findOrFail($subject->id);
    }
}
