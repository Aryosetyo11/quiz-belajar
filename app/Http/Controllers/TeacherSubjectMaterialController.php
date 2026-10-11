<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class TeacherSubjectMaterialController extends Controller
{
    public function index(Request $request, Subject $subject): View
    {
        $subject = $request->user()->subjectsAsTeacher()->findOrFail($subject->id);
        $materials = $subject->materials()->latest()->get();

        return view('portal.teacher-materials', [
            'subject' => $subject,
            'materials' => $materials,
        ]);
    }

    public function store(Request $request, Subject $subject): RedirectResponse
    {
        $subject = $request->user()->subjectsAsTeacher()->findOrFail($subject->id);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', File::types(['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'])->max('20mb')],
        ]);
        $uploadedFile = $validated['file'];
        $filePath = $uploadedFile->store('subject-materials/'.$subject->id, 'local');

        if (! is_string($filePath)) {
            return back()->withErrors(['file' => 'File materi gagal disimpan. Silakan coba lagi.']);
        }

        $subject->materials()->create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'original_file_name' => $uploadedFile->getClientOriginalName(),
            'file_size' => $uploadedFile->getSize(),
        ]);

        return redirect()->route('teacher.subjects.materials', $subject)
            ->with('status', 'Materi berhasil ditambahkan.');
    }

    public function destroy(Request $request, Subject $subject, SubjectMaterial $material): RedirectResponse
    {
        $subject = $request->user()->subjectsAsTeacher()->findOrFail($subject->id);
        $material = $subject->materials()->findOrFail($material->id);

        Storage::disk('local')->delete($material->file_path);
        $material->delete();

        return redirect()->route('teacher.subjects.materials', $subject)
            ->with('status', 'Materi berhasil dihapus.');
    }
}
