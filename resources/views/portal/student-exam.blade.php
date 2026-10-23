@extends('layouts.portal')

@section('title', $exam->title.' | RuangUji')

@section('content')
    <a href="{{ route('student.exams') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Kembali ke ujian saya</a>
    <header class="mt-5 rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">{{ $exam->subject->name }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $exam->title }}</h1>
        @if ($exam->description)<p class="mt-3 whitespace-pre-line text-base leading-7 text-slate-600">{{ $exam->description }}</p>@endif
        <p class="mt-3 text-sm text-slate-600">{{ $exam->questions->count() }} soal · Durasi {{ $exam->duration_minutes }} menit · Dimulai {{ $attempt->started_at->format('d M Y, H:i') }}</p>
        @php($deadline = $attempt->started_at->copy()->addMinutes($exam->duration_minutes))
        @if ($exam->ends_at && $exam->ends_at->lt($deadline)) @php($deadline = $exam->ends_at) @endif
        <p class="mt-3 inline-flex rounded-lg bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-900" data-exam-countdown="{{ $deadline->timestamp * 1000 }}">Sisa waktu: <time class="ml-1" data-countdown-output>--:--</time></p>
    </header>
    @if ($errors->any())<div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-base text-rose-800" role="alert">{{ $errors->first() }}</div>@endif
    <form method="POST" action="{{ route('student.exams.submit', $exam) }}" class="mt-6 space-y-4" data-exam-submit>
        @csrf
        @foreach ($exam->questions as $index => $question)
            <fieldset class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <legend class="px-1 text-base font-semibold text-slate-950">Soal {{ $index + 1 }} <span class="text-sm font-normal text-slate-500">({{ $question->points }} poin)</span></legend>
                <p class="mt-2 whitespace-pre-line text-base leading-7 text-slate-800">{{ $question->prompt }}</p>
                <div class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($question->options as $key => $option)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 px-3.5 py-3 text-sm text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50/50"><input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" @checked(old('answers.'.$question->id) === $key) class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-700"><span><strong class="mr-1">{{ $key }}.</strong>{{ $option }}</span></label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900">Pastikan semua jawaban sudah benar. Setelah dikirim, jawaban tidak dapat diubah.</div>
        <button type="submit" class="rounded-lg bg-indigo-700 px-5 py-3 text-base font-semibold text-white transition hover:bg-indigo-800">Kirim jawaban</button>
    </form>
@endsection
