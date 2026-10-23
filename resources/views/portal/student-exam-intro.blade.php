@extends('layouts.portal')

@section('title', $exam->title.' | RuangUji')

@section('content')
    <a href="{{ route('student.exams') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Kembali ke ujian saya</a>
    <section class="mx-auto mt-6 max-w-3xl rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">{{ $exam->subject->name }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $exam->title }}</h1>
        @if ($exam->description)<p class="mt-4 whitespace-pre-line text-base leading-7 text-slate-600">{{ $exam->description }}</p>@endif
        <div class="mt-6 grid gap-3 rounded-lg bg-slate-50 p-4 text-sm text-slate-700 sm:grid-cols-2">
            <p><strong>{{ $exam->questions_count }}</strong> soal pilihan ganda</p>
            <p>Durasi <strong>{{ $exam->duration_minutes }} menit</strong></p>
            <p>{{ $exam->starts_at ? 'Dibuka '.$exam->starts_at->format('d M Y, H:i') : 'Waktu mulai fleksibel' }}</p>
            <p>{{ $exam->ends_at ? 'Ditutup '.$exam->ends_at->format('d M Y, H:i') : 'Tidak ada batas akhir jadwal' }}</p>
        </div>
        <p class="mt-5 text-sm leading-6 text-slate-600">Timer dimulai saat Anda menekan tombol mulai. Setelah jawaban dikirim, Anda tidak dapat mengulang ujian.</p>
        <form method="POST" action="{{ route('student.exams.start', $exam) }}" class="mt-6">
            @csrf
            <button type="submit" class="rounded-lg bg-indigo-700 px-5 py-3 text-base font-semibold text-white transition hover:bg-indigo-800">Mulai mengerjakan</button>
        </form>
    </section>
@endsection
