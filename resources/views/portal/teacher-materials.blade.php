@extends('layouts.portal')

@section('title', 'Materi '.$subject->name.' | RuangUji')

@section('content')
    <a href="{{ route('teacher.subjects') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-700">&larr; Kembali ke mata pelajaran</a>
    <header class="mt-5"><p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Materi pembelajaran</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $subject->name }}</h1><p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Tambahkan dokumen atau video untuk siswa yang terdaftar di kelas ini.</p></header>
    @if (session('status'))<div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('status') }}</div>@endif

    <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="materials-title">
            <div class="flex items-center justify-between gap-3"><h2 id="materials-title" class="text-xl font-bold text-slate-950">Materi kelas</h2><span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-800">{{ $materials->count() }} materi</span></div>
            @if ($materials->isEmpty())<div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-7 text-center"><h3 class="font-bold text-slate-900">Belum ada materi</h3><p class="mt-2 text-sm leading-6 text-slate-600">Dokumen atau video yang ditambahkan akan terlihat siswa di kelas ini.</p></div>
            @else
                <ul class="mt-4 divide-y divide-slate-100">@foreach ($materials as $material)<li class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between"><div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-900">{{ $material->title }}</h3><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $material->isVideo() ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700' }}">{{ $material->isVideo() ? 'Video' : 'Dokumen' }}</span></div><p class="mt-1 text-sm text-slate-600">{{ $material->isVideo() ? 'Tautan YouTube/Vimeo' : $material->original_file_name.' - '.number_format($material->file_size / 1024, 0).' KB' }}</p>@if ($material->description)<p class="mt-2 text-sm leading-6 text-slate-600">{{ $material->description }}</p>@endif</div><div class="flex shrink-0 items-center gap-2">@if ($material->isVideo())<a href="{{ $material->video_url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buka tautan</a>@else<a href="{{ route('subject-materials.download', [$subject, $material]) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Unduh</a>@endif<form method="POST" action="{{ route('teacher.subjects.materials.destroy', [$subject, $material]) }}" onsubmit="return confirm('Hapus materi ini?')">@csrf @method('DELETE')<button class="rounded-lg px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-50">Hapus</button></form></div></li>@endforeach</ul>
            @endif
        </section>

        <aside class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="add-material-title">
            <h2 id="add-material-title" class="text-xl font-bold text-slate-950">Tambah materi</h2><p class="mt-2 text-sm leading-6 text-slate-600">Pilih dokumen maksimal 20 MB atau tautan video publik YouTube/Vimeo.</p>
            <form method="POST" action="{{ route('teacher.subjects.materials.store', $subject) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div><label for="material-title" class="mb-1.5 block text-sm font-semibold text-slate-700">Judul materi</label><input id="material-title" name="title" value="{{ old('title') }}" required maxlength="160" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600" placeholder="Contoh: Latihan persamaan"></div>
                @error('title')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                <div><label for="material-description" class="mb-1.5 block text-sm font-semibold text-slate-700">Keterangan <span class="font-normal text-slate-500">(opsional)</span></label><textarea id="material-description" name="description" rows="3" maxlength="2000" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600" placeholder="Ringkasan materi">{{ old('description') }}</textarea></div>
                @error('description')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                <div><label for="material-type" class="mb-1.5 block text-sm font-semibold text-slate-700">Jenis materi</label><select id="material-type" name="type" data-material-type class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600"><option value="file" @selected(old('type', 'file') === 'file')>Dokumen</option><option value="video" @selected(old('type') === 'video')>Video dari tautan</option></select></div>
                @error('type')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                <div data-material-file-wrap><label for="material-file" class="mb-1.5 block text-sm font-semibold text-slate-700">Pilih dokumen</label><input id="material-file" name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt" data-material-file class="w-full rounded-xl border border-slate-300 bg-white p-2 text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-semibold"></div>
                @error('file')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                <div class="hidden" data-material-video-wrap><label for="material-video-url" class="mb-1.5 block text-sm font-semibold text-slate-700">Tautan YouTube atau Vimeo</label><input id="material-video-url" name="video_url" type="url" value="{{ old('video_url') }}" placeholder="https://youtu.be/..." data-material-video-url class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600"><p class="mt-1.5 text-xs leading-5 text-slate-500">Siswa akan menonton video langsung di portal.</p></div>
                @error('video_url')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                <button type="submit" class="w-full rounded-xl bg-indigo-700 px-4 py-3 text-sm font-bold text-white transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">Simpan materi</button>
            </form>
        </aside>
    </div>
@endsection
