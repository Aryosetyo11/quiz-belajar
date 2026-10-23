@extends('layouts.portal')

@section('title', 'Buat Ujian | RuangUji')

@section('content')
    <header>
        <a href="{{ route('teacher.exam-sessions') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Kembali ke sesi ujian</a>
        <p class="mt-5 text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Pembuatan evaluasi</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Buat ujian</h1>
        <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Impor file berisi soal dan kunci untuk mengisi formulir otomatis, atau tulis soal pilihan ganda secara manual.</p>
    </header>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800" role="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-base text-rose-800" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="mt-8 rounded-xl border border-indigo-100 bg-indigo-50/70 p-5 sm:p-6" aria-labelledby="import-heading">
        <h2 id="import-heading" class="text-lg font-semibold text-slate-950">Impor soal dari file</h2>
        <p class="mt-1 text-sm leading-6 text-slate-600">Pilih PDF berbasis teks atau Excel .xlsx. Soal akan ditampilkan untuk diperiksa sebelum disimpan.</p>
        <form method="POST" action="{{ route('teacher.exam-sessions.import') }}" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1"><label for="import-file" class="mb-2 block text-sm font-medium text-slate-800">File soal (maks. 10 MB)</label><input id="import-file" name="file" type="file" accept=".pdf,.xlsx,application/pdf,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700"></div>
            <button type="submit" class="rounded-lg border border-indigo-700 px-4 py-2.5 text-sm font-semibold text-indigo-800 transition hover:bg-white">Baca dan tampilkan soal</button>
        </form>
        <details class="mt-4 text-sm text-slate-700">
            <summary class="cursor-pointer font-semibold">Lihat format file yang didukung</summary>
            <div class="mt-3 space-y-2 leading-6">
                <p><strong>Excel:</strong> baris pertama berisi header <code>Pertanyaan, A, B, C, D, Kunci, Poin</code>. Kolom Poin opsional. Data soal dimulai dari baris kedua.</p>
                <p><strong>PDF:</strong> susun tiap soal sebagai nomor, pertanyaan, pilihan A–D, lalu <code>Kunci: B</code>. PDF hasil scan dan soal esai belum didukung.</p>
            </div>
        </details>
    </section>

    @if ($subjects->isEmpty())
        <p class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-5 text-base text-amber-900">Anda belum memiliki mata pelajaran. <a href="{{ route('teacher.subjects') }}" class="font-semibold underline">Buat mata pelajaran</a> sebelum menyusun ujian.</p>
    @else
        <form id="exam-form" method="POST" action="{{ route('teacher.exam-sessions.store') }}" class="mt-8 space-y-6">
            @csrf
            <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-slate-950">Informasi ujian</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2"><label for="subject-id" class="mb-2 block text-sm font-medium text-slate-800">Mata pelajaran</label><select id="subject-id" name="subject_id" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base"><option value="">Pilih mata pelajaran</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}</option>@endforeach</select></div>
                    <div class="sm:col-span-2"><label for="exam-title" class="mb-2 block text-sm font-medium text-slate-800">Judul ujian</label><input id="exam-title" name="title" value="{{ old('title') }}" required maxlength="160" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base" placeholder="Contoh: Ujian Bab 1"></div>
                    <div class="sm:col-span-2"><label for="description" class="mb-2 block text-sm font-medium text-slate-800">Petunjuk <span class="font-normal text-slate-500">(opsional)</span></label><textarea id="description" name="description" rows="2" maxlength="2000" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base">{{ old('description') }}</textarea></div>
                    <div><label for="duration" class="mb-2 block text-sm font-medium text-slate-800">Durasi (menit)</label><input id="duration" type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="1" max="300" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base"></div>
                    <div class="flex items-center gap-3 pt-7"><input id="published" type="checkbox" name="is_published" value="1" @checked(old('is_published')) class="h-4 w-4 rounded border-slate-300 text-indigo-700"><label for="published" class="text-sm font-medium text-slate-800">Publikasikan agar siswa dapat mengerjakan</label></div>
                    <div><label for="starts-at" class="mb-2 block text-sm font-medium text-slate-800">Waktu mulai <span class="font-normal text-slate-500">(opsional)</span></label><input id="starts-at" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base"></div>
                    <div><label for="ends-at" class="mb-2 block text-sm font-medium text-slate-800">Waktu berakhir <span class="font-normal text-slate-500">(opsional)</span></label><input id="ends-at" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base"></div>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" data-exam-authoring>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><h2 class="text-lg font-semibold text-slate-950">Daftar soal</h2><p class="mt-1 text-sm text-slate-600">Pilihan ganda A–D, nilai maksimal 100 poin per soal.</p></div>
                    <button type="button" data-add-question class="rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">+ Tambah soal</button>
                </div>
                <input type="hidden" id="questions-json" name="questions_json" value="">
                <p data-question-error class="mt-4 text-sm text-rose-700" role="alert"></p>
                <div data-question-list class="mt-4 space-y-4"></div>
                <script type="application/json" data-imported-questions>{{ Illuminate\Support\Js::encode(old('questions_json') ? json_decode(old('questions_json'), true) : $importedQuestions) }}</script>
            </section>
            <button type="submit" class="w-full rounded-lg bg-indigo-700 px-5 py-3.5 text-base font-semibold text-white transition hover:bg-indigo-800 sm:w-auto">Simpan ujian</button>
        </form>
    @endif
@endsection
