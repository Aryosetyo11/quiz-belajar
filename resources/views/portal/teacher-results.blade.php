@extends('layouts.portal')

@section('title', 'Hasil Penilaian | RuangUji')

@section('content')
    <div class="space-y-7">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">Panel Pengajar</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Hasil penilaian</h1>
                <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Periksa pengerjaan yang sudah dikumpulkan, prioritaskan esai yang menunggu nilai, dan tinjau hasil PG yang sudah dikoreksi otomatis.</p>
            </div>
            <a href="{{ route('teacher.exam-sessions') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kelola sesi ujian</a>
        </header>

        <section class="grid gap-4 sm:grid-cols-3" aria-label="Ringkasan penilaian">
            <a href="{{ route('teacher.results', ['status' => 'pending', 'exam_id' => $selectedExamId, 'search' => $search]) }}" class="rounded-xl border border-amber-200 bg-amber-50 p-5 transition hover:border-amber-300 hover:shadow-sm">
                <p class="text-sm font-semibold text-amber-900">Esai menunggu nilai</p>
                <p class="mt-2 text-3xl font-bold text-amber-950">{{ $pendingCount }}</p>
                <p class="mt-1 text-xs text-amber-800">Buka daftar untuk mulai memeriksa</p>
            </a>
            <a href="{{ route('teacher.results', ['status' => 'graded', 'exam_id' => $selectedExamId, 'search' => $search]) }}" class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 transition hover:border-emerald-300 hover:shadow-sm">
                <p class="text-sm font-semibold text-emerald-900">Sudah dinilai</p>
                <p class="mt-2 text-3xl font-bold text-emerald-950">{{ $gradedCount }}</p>
                <p class="mt-1 text-xs text-emerald-800">Termasuk penilaian esai guru</p>
            </a>
            <a href="{{ route('teacher.results', ['status' => 'automatic', 'exam_id' => $selectedExamId, 'search' => $search]) }}" class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm">
                <p class="text-sm font-semibold text-slate-700">PG dinilai otomatis</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $automaticCount }}</p>
                <p class="mt-1 text-xs text-slate-500">Tidak memerlukan koreksi manual</p>
            </a>
        </section>

        <form method="GET" action="{{ route('teacher.results') }}" class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-[minmax(0,1fr)_220px_200px_auto] md:items-end">
            <div>
                <label for="result-search" class="mb-1.5 block text-sm font-semibold text-slate-800">Cari siswa</label>
                <input id="result-search" name="search" value="{{ $search }}" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Nama atau NISN">
            </div>
            <div>
                <label for="result-exam" class="mb-1.5 block text-sm font-semibold text-slate-800">Sesi ujian</label>
                <select id="result-exam" name="exam_id" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    <option value="">Semua sesi</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" @selected($selectedExamId === $session->id)>{{ $session->subject->name }} · {{ $session->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="result-status" class="mb-1.5 block text-sm font-semibold text-slate-800">Status penilaian</label>
                <select id="result-status" name="status" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">
                    <option value="pending" @selected($selectedStatus === 'pending')>Menunggu penilaian</option>
                    <option value="graded" @selected($selectedStatus === 'graded')>Sudah dinilai</option>
                    <option value="automatic" @selected($selectedStatus === 'automatic')>PG otomatis</option>
                    <option value="all" @selected($selectedStatus === 'all')>Semua pengumpulan</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tampilkan</button>
                @if ($search !== '' || $selectedExamId !== null || $selectedStatus !== 'pending')
                    <a href="{{ route('teacher.results') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                @endif
            </div>
        </form>

        <section aria-labelledby="submission-list-heading">
            <div class="mb-4 flex items-end justify-between gap-3">
                <div>
                    <h2 id="submission-list-heading" class="text-xl font-semibold text-slate-950">
                        {{ match ($selectedStatus) { 'pending' => 'Perlu diperiksa', 'graded' => 'Sudah dinilai', 'automatic' => 'Nilai otomatis', default => 'Semua pengumpulan' } }}
                    </h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $attempts->total() }} pengerjaan ditemukan</p>
                </div>
            </div>

            @if ($attempts->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($attempts as $attempt)
                        @php
                            $attemptQuestionCount = count($attempt->question_ids ?? []) ?: max((int) ($attempt->exam->question_count ?? 0), $attempt->exam->questions->count());
                            $essayCount = max((int) ($attempt->exam->essay_question_count ?? 0), $attempt->exam->questions->where('type', \App\Models\ExamQuestion::TYPE_ESSAY)->count());
                            $isWaitingForEssayGrade = $attempt->graded_at === null && ($attempt->score === null || $essayCount > 0);
                        @endphp
                        <article class="rounded-xl border border-slate-200 bg-white p-5 transition hover:border-indigo-200 hover:shadow-sm sm:p-6">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold text-slate-950">{{ $attempt->student->name ?? 'Siswa' }}</h3>
                                        @if ($isWaitingForEssayGrade)
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">Menunggu nilai esai</span>
                                        @elseif ($attempt->graded_at !== null)
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-900">Sudah dinilai</span>
                                        @else
                                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-800">Dinilai otomatis</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ $attempt->student->nisn ? 'NISN '.$attempt->student->nisn.' · ' : '' }}{{ $attempt->exam->subject->name }} · {{ $attempt->exam->title }}</p>
                                    <p class="mt-2 text-sm text-slate-500">Dikumpulkan {{ $attempt->submitted_at->format('d M Y, H:i') }} · {{ $attemptQuestionCount > 0 ? $attemptQuestionCount : '—' }} soal{{ $essayCount > 0 ? ' · '.$essayCount.' esai' : '' }}</p>
                                </div>
                                <div class="flex items-center justify-between gap-3 sm:justify-end">
                                    @if ($attempt->score !== null)
                                        <p class="text-right"><span class="block text-xs font-semibold text-slate-500">Nilai</span><span class="text-2xl font-bold text-slate-950">{{ $attempt->score }}<span class="text-sm font-medium text-slate-500">/100</span></span></p>
                                    @else
                                        <p class="text-sm font-medium text-amber-800">Belum ada nilai akhir</p>
                                    @endif
                                    <a href="{{ route('teacher.results.review', $attempt) }}" class="inline-flex shrink-0 items-center rounded-lg {{ $isWaitingForEssayGrade ? 'bg-indigo-700 text-white hover:bg-indigo-800' : 'border border-slate-300 text-slate-700 hover:bg-slate-50' }} px-4 py-2.5 text-sm font-semibold transition">
                                        {{ $isWaitingForEssayGrade ? 'Periksa esai' : 'Lihat hasil' }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-5">{{ $attempts->links() }}</div>
            @else
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                    <h3 class="text-lg font-semibold text-slate-950">
                        {{ $selectedStatus === 'pending' ? 'Tidak ada esai yang menunggu penilaian' : 'Belum ada hasil untuk filter ini' }}
                    </h3>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-600">{{ $selectedStatus === 'pending' ? 'Pengumpulan dengan jawaban esai akan muncul di sini setelah siswa menyelesaikan ujian.' : 'Coba ubah sesi, status, atau kata kunci pencarian.' }}</p>
                </div>
            @endif
        </section>
    </div>
@endsection
