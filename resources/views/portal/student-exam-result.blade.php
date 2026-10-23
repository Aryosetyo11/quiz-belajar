@extends('layouts.portal')

@section('title', 'Hasil '.$exam->title.' | RuangUji')

@section('content')
    <a href="{{ route('student.exams') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Kembali ke ujian saya</a>
    @if (session('status'))<div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800" role="status">{{ session('status') }}</div>@endif
    <section class="mt-5 rounded-xl border border-slate-200 bg-white p-6 text-center sm:p-10">
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">{{ $exam->subject->name }}</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-950">{{ $exam->title }}</h1>
        <p class="mt-7 text-sm font-medium text-slate-500">Nilai Anda</p>
        <p class="mt-1 text-6xl font-bold tracking-tight text-indigo-800">{{ $attempt->score }}<span class="text-2xl">/100</span></p>
        <p class="mt-4 text-sm text-slate-600">Dikirim {{ $attempt->submitted_at->format('d M Y, H:i') }}</p>
        <p class="mt-2 text-sm text-slate-600">Jawaban dan kunci soal tidak ditampilkan. Hubungi guru jika membutuhkan pembahasan.</p>
        <a href="{{ route('student.exams') }}" class="mt-6 inline-flex rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">Kembali ke daftar ujian</a>
    </section>
@endsection
