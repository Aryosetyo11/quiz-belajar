@extends('layouts.portal')

@section('title', 'Materi '.$subject->name.' | RuangUji')

@section('content')
    <a href="{{ route('teacher.subjects') }}" class="text-base font-medium text-slate-600 underline-offset-4 hover:text-slate-900 hover:underline">← Kembali ke mata pelajaran</a>
    <header class="mt-5">
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Materi pembelajaran</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $subject->name }}</h1>
        <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Tambahkan file belajar yang dapat diakses siswa terdaftar.</p>
    </header>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
        <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="materials-title">
            <div class="flex items-baseline justify-between gap-3">
                <h2 id="materials-title" class="text-xl font-semibold text-slate-950">Materi tersedia</h2>
                <span class="text-sm text-slate-500">{{ $materials->count() }} file</span>
            </div>

            @if ($materials->isEmpty())
                <div class="mt-5 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                    <h3 class="text-lg font-semibold text-slate-900">Belum ada materi</h3>
                    <p class="mt-2 text-base leading-7 text-slate-600">Materi yang Anda unggah akan tampil di sini untuk siswa terdaftar.</p>
                </div>
            @else
                <ul class="mt-4 divide-y divide-slate-100">
                    @foreach ($materials as $material)
                        <li class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-semibold text-slate-900">{{ $material->title }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $material->original_file_name }} · {{ number_format($material->file_size / 1024, 0) }} KB</p>
                                @if ($material->description)
                                    <p class="mt-2 text-base leading-6 text-slate-600">{{ $material->description }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <a href="{{ route('subject-materials.download', [$subject, $material]) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Unduh</a>
                                <form method="POST" action="{{ route('teacher.subjects.materials.destroy', [$subject, $material]) }}" onsubmit="return confirm('Hapus materi ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Hapus</button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="upload-material-title">
            <h2 id="upload-material-title" class="text-xl font-semibold text-slate-950">Tambah materi</h2>
            <p class="mt-2 text-base leading-7 text-slate-600">PDF, Word, PowerPoint, Excel, atau TXT. Maksimal 20 MB per file.</p>

            <form method="POST" action="{{ route('teacher.subjects.materials.store', $subject) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="material-title" class="mb-2 block text-base font-medium text-slate-800">Judul materi</label>
                    <input id="material-title" name="title" type="text" value="{{ old('title') }}" required maxlength="160" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100" placeholder="Contoh: Bab 1 — Bilangan">
                    @error('title')
                        <p class="mt-2 text-sm text-rose-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="material-description" class="mb-2 block text-base font-medium text-slate-800">Keterangan <span class="font-normal text-slate-500">(opsional)</span></label>
                    <textarea id="material-description" name="description" rows="3" maxlength="2000" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100" placeholder="Isi singkat materi">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-rose-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="material-file" class="mb-2 block text-base font-medium text-slate-800">File materi</label>
                    <input id="material-file" name="file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-slate-700">
                    @error('file')
                        <p class="mt-2 text-sm text-rose-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-3 text-base font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Unggah materi</button>
            </form>
        </aside>
    </div>
@endsection
