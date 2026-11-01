@extends('layouts.portal')

@section('title', 'Hasil Belajar | RuangUji')

@section('content')
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Rekap pembelajaran</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Hasil belajar</h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Lihat nilai ujian yang sudah dinilai dan pantau jawaban esai yang masih menunggu penilaian.</p>
        </div>
        <a href="{{ route('student.exams') }}" class="inline-flex w-fit items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali ke daftar ujian</a>
    </header>

    <section class="mt-7 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan hasil belajar">
        <article class="rounded-xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-semibold text-slate-600">Ujian selesai</p>
            <p class="mt-1 text-2xl font-bold text-slate-950">{{ $completedCount }}</p>
            <p class="mt-1 text-sm text-slate-600">Jawaban sudah dikirim</p>
        </article>
        <article class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-4">
            <p class="text-sm font-semibold text-indigo-800">Rata-rata nilai</p>
            <p class="mt-1 text-2xl font-bold text-slate-950">{{ $averageScore !== null ? number_format($averageScore, 1).' / 100' : '—' }}</p>
            <p class="mt-1 text-sm text-slate-700">Dari ujian yang sudah dinilai</p>
        </article>
        <article class="rounded-xl border border-amber-100 bg-amber-50/70 p-4">
            <p class="text-sm font-semibold text-amber-900">Menunggu penilaian</p>
            <p class="mt-1 text-2xl font-bold text-slate-950">{{ $pendingGradingCount }}</p>
            <p class="mt-1 text-sm text-slate-700">Jawaban esai diperiksa pengajar</p>
        </article>
    </section>

    <nav class="mt-7 flex flex-wrap gap-2" aria-label="Filter hasil ujian">
        <a href="{{ route('student.results', ['status' => 'all']) }}" @class(['rounded-full px-4 py-2 text-sm font-semibold transition', 'bg-indigo-700 text-white' => $status === 'all', 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $status !== 'all'])>Semua <span class="ml-1 opacity-80">{{ $completedCount }}</span></a>
        <a href="{{ route('student.results', ['status' => 'graded']) }}" @class(['rounded-full px-4 py-2 text-sm font-semibold transition', 'bg-indigo-700 text-white' => $status === 'graded', 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $status !== 'graded'])>Nilai tersedia <span class="ml-1 opacity-80">{{ $gradedCount }}</span></a>
        <a href="{{ route('student.results', ['status' => 'pending']) }}" @class(['rounded-full px-4 py-2 text-sm font-semibold transition', 'bg-indigo-700 text-white' => $status === 'pending', 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50' => $status !== 'pending'])>Menunggu penilaian <span class="ml-1 opacity-80">{{ $pendingGradingCount }}</span></a>
    </nav>

    @if ($attempts->isNotEmpty())
        <section class="mt-4 space-y-3" aria-label="Daftar hasil ujian">
            @foreach ($attempts as $attempt)
                <article class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-indigo-700">{{ $attempt->exam->subject->name }}</p>
                        <h2 class="mt-1 break-words text-lg font-bold leading-6 text-slate-950">{{ $attempt->exam->title }}</h2>
                        <p class="mt-2 text-sm text-slate-600">Dikumpulkan {{ $attempt->submitted_at->format('d M Y, H:i') }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                        @if ($attempt->score !== null)
                            <div class="rounded-xl bg-emerald-50 px-4 py-2.5 sm:text-right">
                                <p class="text-sm font-semibold text-emerald-800">Nilai</p>
                                <p class="mt-0.5 text-xl font-bold text-emerald-950">{{ $attempt->score }}<span class="text-sm font-medium text-emerald-800">/100</span></p>
                            </div>
                        @else
                            <span class="rounded-xl bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">Menunggu nilai esai</span>
                        @endif
                        <a href="{{ route('student.exams.show', $attempt->exam) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-800 transition hover:bg-slate-50">
                            Lihat detail
                        </a>
                    </div>
                </article>
            @endforeach
        </section>

        @if ($attempts->hasPages())
            <div class="mt-6">{{ $attempts->links() }}</div>
        @endif
    @else
        <section class="mt-4 rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-8" aria-label="Belum ada hasil ujian">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700" aria-hidden="true">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
            </span>
            @if ($completedCount === 0)
                <h2 class="mt-4 text-xl font-semibold text-slate-950">Belum ada hasil ujian</h2>
                <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Setelah kamu mengirim jawaban ujian, nilai dan status penilaiannya akan tampil di sini.</p>
                <a href="{{ route('student.exams') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-3 text-base font-semibold text-white transition hover:bg-indigo-800">Lihat ujian saya</a>
            @else
                <h2 class="mt-4 text-xl font-semibold text-slate-950">Tidak ada hasil pada filter ini</h2>
                <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Coba buka kategori lain untuk melihat hasil ujianmu.</p>
            @endif
        </section>
    @endif
@endsection
