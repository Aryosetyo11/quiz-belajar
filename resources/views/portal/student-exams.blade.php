@extends('layouts.portal')

@section('title', 'Ujian Saya | RuangUji')

@section('content')
    <header>
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Evaluasi pembelajaran</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Ujian saya</h1>
        <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Ujian yang tersedia dari mata pelajaran tempat Anda terdaftar.</p>
    </header>

    @if ($errors->has('exam'))<div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-base text-amber-900" role="alert">{{ $errors->first('exam') }}</div>@endif

    <div class="mt-8 grid gap-4 md:grid-cols-2">
        @forelse ($exams as $exam)
            @php($attempt = $exam->attempts->first())
            <article class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <div class="flex items-start justify-between gap-4">
                    <div><p class="text-sm font-medium text-indigo-700">{{ $exam->subject->name }}</p><h2 class="mt-1 text-lg font-semibold text-slate-950">{{ $exam->title }}</h2></div>
                    @if ($attempt?->submitted_at)<span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-sm font-medium text-emerald-800">Selesai</span>@else<span class="shrink-0 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-900">{{ $exam->starts_at?->isFuture() ? 'Terjadwal' : 'Tersedia' }}</span>@endif
                </div>
                @if ($exam->description)<p class="mt-3 text-sm leading-6 text-slate-600">{{ $exam->description }}</p>@endif
                <p class="mt-4 text-sm text-slate-600">{{ $exam->questions_count }} soal · {{ $exam->duration_minutes }} menit</p>
                <p class="mt-1 text-sm text-slate-500">{{ $exam->starts_at ? 'Mulai '.$exam->starts_at->format('d M Y, H:i') : 'Waktu mulai fleksibel' }}{{ $exam->ends_at ? ' · Selesai '.$exam->ends_at->format('d M Y, H:i') : '' }}</p>
                @if ($attempt?->submitted_at)<p class="mt-3 text-sm font-semibold text-slate-800">Nilai: {{ $attempt->score }}/100</p>@endif
                <a href="{{ route('student.exams.show', $exam) }}" class="mt-5 inline-flex rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">{{ $attempt?->submitted_at ? 'Lihat hasil' : ($attempt ? 'Lanjutkan ujian' : 'Mulai ujian') }}</a>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center md:col-span-2"><h2 class="text-lg font-semibold text-slate-950">Belum ada ujian</h2><p class="mt-2 text-base text-slate-600">Ujian yang dipublikasikan oleh guru akan muncul di sini.</p></div>
        @endforelse
    </div>
@endsection
