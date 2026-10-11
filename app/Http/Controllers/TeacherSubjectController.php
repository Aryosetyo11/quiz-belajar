<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherSubjectController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = $request->user()
            ->subjectsAsTeacher()
            ->withCount('students')
            ->withCount('materials')
            ->with('students:id,name,nisn')
            ->orderBy('name')
            ->get();

        return view('portal.teacher-subjects', ['subjects' => $subjects]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->subjectsAsTeacher()->create($validated);

        return redirect()->route('teacher.subjects')->with('status', 'Mata pelajaran berhasil dibuat.');
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject = $this->ownedSubject($request, $subject);
        $subjectInput = 'subjects.'.$subject->id;
        $validated = $request->validate([
            $subjectInput.'.name' => ['required', 'string', 'max:120'],
            $subjectInput.'.description' => ['nullable', 'string', 'max:1000'],
        ]);

        $subject->update($validated['subjects'][$subject->id]);

        return redirect()->route('teacher.subjects')->with('status', 'Mata pelajaran berhasil diperbarui.');
    }

    public function enrollStudent(Request $request, Subject $subject): RedirectResponse
    {
        $subject = $this->ownedSubject($request, $subject);
        $nisnField = 'students.'.$subject->id.'.nisn';
        $validated = $request->validate([
            $nisnField => ['required', 'string', 'regex:/^\d{10}$/'],
        ]);

        $student = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->where('nisn', $validated['students'][$subject->id]['nisn'])
            ->first();

        if (! $student) {
            return back()->withErrors([$nisnField => 'Akun siswa dengan NISN tersebut tidak ditemukan.'])->withInput();
        }

        $syncResult = $subject->students()->syncWithoutDetaching([$student->id]);

        if ($syncResult['attached'] === []) {
            return back()->withErrors([$nisnField => 'Siswa tersebut sudah terdaftar di mata pelajaran ini.'])->withInput();
        }

        return redirect()->route('teacher.subjects')->with('status', $student->name.' berhasil ditambahkan ke '.$subject->name.'.');
    }

    public function removeStudent(Request $request, Subject $subject, User $student): RedirectResponse
    {
        $subject = $this->ownedSubject($request, $subject);
        abort_unless($student->role === User::ROLE_STUDENT, 404);

        $subject->students()->detach($student->id);

        return redirect()->route('teacher.subjects')->with('status', 'Siswa berhasil dikeluarkan dari mata pelajaran.');
    }

    private function ownedSubject(Request $request, Subject $subject): Subject
    {
        return $request->user()->subjectsAsTeacher()->findOrFail($subject->id);
    }
}
