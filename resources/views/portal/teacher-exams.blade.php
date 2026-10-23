@extends('layouts.portal')

@section('title', 'Sesi Ujian | RuangUji')

@section('content')
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Evaluasi pembelajaran</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Sesi ujian</h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Buat ujian pilihan ganda secara manual atau impor soal dari file PDF dan Excel.</p>
        </div>
        <a href="{{ route('teacher.exam-sessions.create') }}" class="rounded-lg bg-indigo-700 px-4 py-3 text-base font-semibold text-white transition hover:bg-indigo-800">Buat ujian</a>
    </header>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    @forelse ($subjects as $subject)
        <section class="mt-8" aria-labelledby="subject-{{ $subject->id }}">
            <h2 id="subject-{{ $subject->id }}" class="text-xl font-semibold text-slate-950">{{ $subject->name }}</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @forelse ($subject->exams as $exam)
                    <article class="rounded-xl border border-slate-200 bg-white p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div><h3 class="text-lg font-semibold text-slate-950">{{ $exam->title }}</h3><p class="mt-1 text-sm text-slate-600">{{ $exam->questions_count }} soal · {{ $exam->duration_minutes }} menit</p></div>
                            <span @class(['rounded-full px-3 py-1 text-sm font-medium', 'bg-emerald-100 text-emerald-800' => $exam->is_published, 'bg-amber-100 text-amber-800' => ! $exam->is_published])>{{ $exam->is_published ? 'Dipublikasikan' : 'Draf' }}</span>
                        </div>
                        @if ($exam->description)<p class="mt-3 text-sm leading-6 text-slate-600">{{ $exam->description }}</p>@endif
                        <p class="mt-3 text-sm text-slate-500">{{ $exam->starts_at ? 'Mulai '.$exam->starts_at->format('d M Y H:i') : 'Jadwal fleksibel' }}{{ $exam->ends_at ? ' · Berakhir '.$exam->ends_at->format('d M Y H:i') : '' }}</p>
                    </article>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-300 bg-white p-5 text-base text-slate-600">Belum ada ujian pada mata pelajaran ini.</p>
                @endforelse
            </div>
        </section>
    @empty
        <section class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <h2 class="text-lg font-semibold text-slate-950">Buat mata pelajaran lebih dahulu</h2>
            <p class="mt-2 text-base text-slate-600">Ujian harus terhubung dengan mata pelajaran yang Anda kelola.</p>
            <a href="{{ route('teacher.subjects') }}" class="mt-4 inline-flex rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white">Kelola mata pelajaran</a>
        </section>
    @endforelse
@endsection
