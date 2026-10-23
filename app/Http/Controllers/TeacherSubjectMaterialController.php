<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Validator;
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
        $validator = validator($request->all(), [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', Rule::in(['file', 'video'])],
            'file' => [Rule::excludeIf($request->input('type') === 'video'), Rule::requiredIf($request->input('type') === 'file'), File::types(['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'])->max('20mb')],
            'video_url' => [Rule::excludeIf($request->input('type') === 'file'), Rule::requiredIf($request->input('type') === 'video'), 'nullable', 'url', 'max:2048'],
        ]);

        $validator->after(function (Validator $validator) use ($request): void {
            if ($request->input('type') !== 'video' || ! is_string($request->input('video_url'))) {
                return;
            }

            $host = strtolower((string) parse_url($request->input('video_url'), PHP_URL_HOST));

            if (! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com', 'youtu.be', 'www.youtu.be', 'vimeo.com', 'www.vimeo.com'], true)) {
                $validator->errors()->add('video_url', 'Gunakan tautan video dari YouTube atau Vimeo.');
            }
        });

        $validated = $validator->validate();
        $materialData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'video') {
            $materialData['video_url'] = $validated['video_url'];
        } else {
            $uploadedFile = $validated['file'];
            $filePath = $uploadedFile->store('subject-materials/'.$subject->id, 'local');

            if (! is_string($filePath)) {
                return back()->withErrors(['file' => 'File materi gagal disimpan. Silakan coba lagi.']);
            }

            $materialData['file_path'] = $filePath;
            $materialData['original_file_name'] = $uploadedFile->getClientOriginalName();
            $materialData['file_size'] = $uploadedFile->getSize();
        }

        $subject->materials()->create($materialData);

        return redirect()->route('teacher.subjects.materials', $subject)
            ->with('status', 'Materi berhasil ditambahkan.');
    }

    public function destroy(Request $request, Subject $subject, SubjectMaterial $material): RedirectResponse
    {
        $subject = $request->user()->subjectsAsTeacher()->findOrFail($subject->id);
        $material = $subject->materials()->findOrFail($material->id);

        if (! $material->isVideo()) {
            Storage::disk('local')->delete($material->file_path);
        }
        $material->delete();

        return redirect()->route('teacher.subjects.materials', $subject)
            ->with('status', 'Materi berhasil dihapus.');
    }
}
