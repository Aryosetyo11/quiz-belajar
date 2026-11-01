<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\SubjectMaterial;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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
            'type' => ['required', Rule::in(['file', 'video', 'link'])],
            'file' => [Rule::excludeIf($request->input('type') !== 'file'), Rule::requiredIf($request->input('type') === 'file'), File::types(['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'])->max('20mb')],
            'video_url' => [Rule::excludeIf($request->input('type') !== 'video'), Rule::requiredIf($request->input('type') === 'video'), 'nullable', 'url', 'max:2048'],
            'external_url' => [Rule::excludeIf($request->input('type') !== 'link'), Rule::requiredIf($request->input('type') === 'link'), 'nullable', 'url', 'max:2048'],
        ]);

        $validator->after(function (Validator $validator) use ($request): void {
            $type = $request->input('type');

            if ($type === 'video' && is_string($request->input('video_url'))) {
                $host = strtolower((string) parse_url($request->input('video_url'), PHP_URL_HOST));
                $scheme = strtolower((string) parse_url($request->input('video_url'), PHP_URL_SCHEME));

                if ($scheme !== 'https' || ! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com', 'youtu.be', 'www.youtu.be', 'vimeo.com', 'www.vimeo.com'], true)) {
                    $validator->errors()->add('video_url', 'Gunakan tautan HTTPS dari YouTube atau Vimeo.');
                }

                return;
            }

            if ($type !== 'link' || ! is_string($request->input('external_url'))) {
                return;
            }

            $url = $request->input('external_url');
            $host = strtolower((string) parse_url($url, PHP_URL_HOST));
            $path = (string) parse_url($url, PHP_URL_PATH);
            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            $isDriveFile = $host === 'drive.google.com'
                && (preg_match('#^/file/d/[A-Za-z0-9_-]+(?:/|$)#', $path) === 1
                    || (str_starts_with($path, '/open') && is_string(parse_url($url, PHP_URL_QUERY)) && str_contains((string) parse_url($url, PHP_URL_QUERY), 'id=')));
            $isGoogleSlides = $host === 'docs.google.com'
                && preg_match('#^/presentation/d/[A-Za-z0-9_-]+(?:/|$)#', $path) === 1;
            $isCanva = in_array($host, ['canva.com', 'www.canva.com'], true)
                && preg_match('#^/design/[A-Za-z0-9_-]+(?:/[A-Za-z0-9_-]+)?/(?:view|watch|present)(?:/|$)#', $path) === 1;
            $isCanvaShortLink = $host === 'canva.link'
                && preg_match('#^/[A-Za-z0-9_-]{6,30}/?$#', $path) === 1;

            if ($scheme !== 'https' || ! ($isDriveFile || $isGoogleSlides || $isCanva || $isCanvaShortLink)) {
                $validator->errors()->add('external_url', 'Gunakan tautan HTTPS file Google Drive, Google Slides, atau desain Canva yang dapat dibagikan.');
            }
        });

        $validated = $validator->validate();

        if ($validated['type'] === 'link' && parse_url($validated['external_url'], PHP_URL_HOST) === 'canva.link') {
            $resolvedCanvaUrl = $this->resolveCanvaShortLink($validated['external_url']);

            if ($resolvedCanvaUrl === null) {
                return back()->withErrors([
                    'external_url' => 'Canva tidak memberikan tautan pratinjau yang bisa dibuka. Dari Canva, pilih Bagikan → Sematkan (Embed), lalu gunakan tautan atau kode semat yang disediakan.',
                ])->withInput();
            }

            $validated['external_url'] = $resolvedCanvaUrl;
        }

        $materialData = [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
        ];

        if ($validated['type'] === 'video') {
            $materialData['video_url'] = $validated['video_url'];
        } elseif ($validated['type'] === 'link') {
            $materialData['external_url'] = $validated['external_url'];
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

        if ($material->type === 'file') {
            Storage::disk('local')->delete($material->file_path);
        }
        $material->delete();

        return redirect()->route('teacher.subjects.materials', $subject)
            ->with('status', 'Materi berhasil dihapus.');
    }

    private function resolveCanvaShortLink(string $url): ?string
    {
        $currentUrl = $url;

        for ($redirectCount = 0; $redirectCount < 5; $redirectCount++) {
            try {
                $response = Http::connectTimeout(3)
                    ->timeout(8)
                    ->withOptions(['allow_redirects' => false])
                    ->get($currentUrl);
            } catch (ConnectionException) {
                return null;
            }

            if (! $response->redirect()) {
                return $this->isCanvaDesignUrl($currentUrl) ? $currentUrl : null;
            }

            $location = $response->header('Location');

            if (! is_string($location) || $location === '') {
                return null;
            }

            $locationParts = parse_url($location);

            if (! is_array($locationParts)) {
                return null;
            }

            if (! isset($locationParts['scheme'], $locationParts['host'])) {
                $currentParts = parse_url($currentUrl);
                $host = $currentParts['host'] ?? null;
                $path = $locationParts['path'] ?? '/';
                $query = isset($locationParts['query']) ? '?'.$locationParts['query'] : '';

                if (! is_string($host)) {
                    return null;
                }

                $location = 'https://'.$host.'/'.ltrim($path, '/').$query;
                $locationParts = parse_url($location);
            }

            $host = strtolower((string) ($locationParts['host'] ?? ''));
            $scheme = strtolower((string) ($locationParts['scheme'] ?? ''));

            if ($scheme !== 'https' || ! in_array($host, ['canva.link', 'canva.com', 'www.canva.com'], true)) {
                return null;
            }

            if ($host !== 'canva.link') {
                return $this->isCanvaDesignUrl($location) ? $location : null;
            }

            $currentUrl = $location;
        }

        return null;
    }

    private function isCanvaDesignUrl(string $url): bool
    {
        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), ['canva.com', 'www.canva.com'], true)
            && preg_match('#^/design/[A-Za-z0-9_-]+(?:/|$)#', (string) parse_url($url, PHP_URL_PATH)) === 1;
    }
}
