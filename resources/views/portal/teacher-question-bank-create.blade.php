@extends('layouts.portal')

@section('title', 'Tambah Soal ke Bank Soal | RuangUji')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <a href="{{ route($questionBankRoutePrefix) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-700 hover:text-indigo-900 transition">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Kembali ke Bank Soal
        </a>
        <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-5">
            <div>
                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">
                    Pustaka Evaluasi
                </span>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Tambah Paket Soal Baru</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Susun butir soal manual dengan navigasi nomor, atau impor langsung dari file Excel/CSV/PDF.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="openImportTab()" class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50/70 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 transition">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Impor dari File
                </button>
            </div>
        </div>
    </div>

    @if ($subjects->isEmpty())
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900">
            <h3 class="font-bold text-base">Belum Ada Mata Pelajaran</h3>
            <p class="mt-1">Belum ada mata pelajaran untuk paket ini. Silakan <a href="{{ $isGlobalQuestionBankManager ? route('admin.assignments') : route('teacher.subjects') }}" class="font-bold underline">buat mata pelajaran</a> terlebih dahulu sebelum menyusun bank soal.</p>
        </div>
    @else
        <form id="exam-form" method="POST" action="{{ route($questionBankRoutePrefix.'.store-manual') }}" class="space-y-6">
            @csrf

            {{-- Informasi Paket Soal --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-xs font-bold text-white">
                        1
                    </span>
                    <h2 class="text-base font-bold text-slate-950">Informasi Paket Soal</h2>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="subject_id" class="mb-1.5 block text-xs font-bold text-slate-700">Mata Pelajaran <span class="text-rose-500">*</span></label>
                        <select id="subject_id" name="subject_id" required class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>{{ $subject->name }}@if ($isGlobalQuestionBankManager) — {{ $subject->school->name ?? 'Sekolah' }} · {{ $subject->classroom->name ?? 'Rombel' }}@endif</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="title" class="mb-1.5 block text-xs font-bold text-slate-700">Nama Paket / Bab Soal <span class="text-rose-500">*</span></label>
                        <input id="title" name="title" value="{{ old('title') }}" required maxlength="160" placeholder="Contoh: Latihan Bab 1 Aljabar, Bank Soal UTS Semester 1" class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="mb-1.5 block text-xs font-bold text-slate-700">Catatan / Topik Materi <span class="font-normal text-slate-400">(opsional)</span></label>
                        <textarea id="description" name="description" rows="2" maxlength="2000" placeholder="Tulis catatan atau lingkup materi soal ini..." class="block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">{{ old('description') }}</textarea>
                    </div>
                </div>
            </section>

            {{-- Penyusunan Butir Soal dengan Navigator Grid --}}
            <section id="questions-section" class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xs" data-exam-authoring>
                {{-- Section Header --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-xs font-bold text-white">
                            2
                        </span>
                        <div>
                            <h2 class="text-base font-bold text-slate-950">Susun Butir Soal (Pilihan Ganda & Esai)</h2>
                            <p class="text-xs text-slate-500">Pilih nomor soal di panel samping untuk berpindah, atau gunakan tombol navigasi di bawah.</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-500 hidden sm:inline">Pilihan ganda atau uraian esai</span>
                    </div>
                </div>

                <input type="hidden" id="questions-json" name="questions_json" value="">
                <p data-question-error class="mt-4 text-sm font-semibold text-rose-700" role="alert"></p>

                {{-- Two-column Layout: Question Card (Left) | Navigator Grid (Right) --}}
                <div class="mt-5 flex flex-col-reverse lg:flex-row gap-5 items-start">
                    {{-- Left / Center: Active Question Form --}}
                    <div class="min-w-0 flex-1 w-full" data-question-card></div>

                    {{-- Right Sidebar: Question Navigator Grid --}}
                    <aside class="w-full lg:w-60 shrink-0 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <div class="flex items-center justify-between border-b border-slate-200/80 pb-2.5 mb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Nomor Soal</span>
                            <span data-question-counter class="rounded-md bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-700">Soal 1 / 1</span>
                        </div>

                        {{-- Number Grid --}}
                        <div data-question-navigator class="grid grid-cols-5 gap-1.5 max-h-72 overflow-y-auto p-1"></div>

                        {{-- Add Question Button --}}
                        <button type="button" data-add-question
                            class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-xl border border-indigo-300 bg-indigo-600 px-3 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-indigo-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            + Tambah Soal
                        </button>

                        {{-- Legend --}}
                        <div class="mt-4 space-y-1.5 border-t border-slate-200/80 pt-3 text-[11px] text-slate-600">
                            <div class="flex items-center gap-2">
                                <span class="flex h-4 w-4 items-center justify-center rounded bg-indigo-700 text-white font-bold text-[9px]">1</span>
                                <span>Sedang dibuka</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-4 w-4 items-center justify-center rounded bg-emerald-100 border border-emerald-400 text-emerald-900 font-bold text-[9px]">1</span>
                                <span>Sudah diisi</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-4 w-4 items-center justify-center rounded bg-slate-100 border border-slate-300 text-slate-700 font-bold text-[9px]">1</span>
                                <span>Belum lengkap</span>
                            </div>
                        </div>
                    </aside>
                </div>

                <script type="application/json" data-imported-questions>[]</script>
            </section>

            {{-- Submit Action Bar --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xs">
                <div class="text-xs text-slate-500">
                    Pastikan seluruh pertanyaan dan pilihan jawaban sudah lengkap sebelum disimpan.
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route($questionBankRoutePrefix) }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </a>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-xs transition hover:bg-indigo-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        Simpan ke Bank Soal
                    </button>
                </div>
            </div>
        </form>
    @endif
</div>

{{-- Modal Impor Soal Cepat --}}
<div id="quick-import-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-xs hidden" role="dialog" aria-modal="true">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900">Impor dari File Excel / CSV / PDF</h3>
            <button type="button" onclick="closeImportTab()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route($questionBankRoutePrefix.'.import') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700">Mata Pelajaran <span class="text-rose-500">*</span></label>
                <select name="subject_id" required class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}@if ($isGlobalQuestionBankManager) — {{ $subject->school->name ?? 'Sekolah' }} · {{ $subject->classroom->name ?? 'Rombel' }}@endif</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Nama Paket Soal <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required maxlength="160" placeholder="Contoh: Paket Soal Bab 1" class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700">Pilih File (.xlsx, .csv, atau .pdf) <span class="text-rose-500">*</span></label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv,.txt,.pdf" required class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white p-2 text-xs text-slate-700">
            </div>

            <div class="mt-5 flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeImportTab()" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">Unggah & Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openImportTab() {
        document.getElementById('quick-import-modal')?.classList.remove('hidden');
    }
    function closeImportTab() {
        document.getElementById('quick-import-modal')?.classList.add('hidden');
    }
</script>
@endsection
