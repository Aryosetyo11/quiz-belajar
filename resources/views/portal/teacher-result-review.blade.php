@extends('layouts.portal')

@section('title', 'Tinjau Hasil · '.$attempt->exam->title)

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <a href="{{ route('teacher.results', request()->query()) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Kembali ke hasil penilaian</a>

        <header class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-indigo-700">{{ $attempt->exam->subject->name }}</p>
                    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">{{ $attempt->exam->title }}</h1>
                    <p class="mt-3 text-base font-semibold text-slate-900">{{ $attempt->student->name }}{{ $attempt->student->nisn ? ' · NISN '.$attempt->student->nisn : '' }}</p>
                    <p class="mt-1 text-sm text-slate-600">Dikumpulkan {{ $attempt->submitted_at->format('d M Y, H:i') }} · {{ $questions->count() }} soal · {{ $essayQuestions->count() }} esai</p>
                </div>
                <div class="rounded-xl {{ $attempt->graded_at ? 'bg-emerald-50' : 'bg-amber-50' }} px-5 py-4 sm:min-w-40 sm:text-right">
                    <p class="text-sm font-semibold {{ $attempt->graded_at ? 'text-emerald-800' : 'text-amber-900' }}">{{ $attempt->graded_at ? 'Nilai akhir' : 'Status nilai' }}</p>
                    @if ($attempt->score !== null)
                        <p class="mt-1 text-3xl font-bold {{ $attempt->graded_at ? 'text-emerald-950' : 'text-slate-950' }}">{{ $attempt->score }}<span class="text-base font-medium text-slate-500">/100</span></p>
                    @else
                        <p class="mt-1 text-lg font-bold text-amber-950">Menunggu esai</p>
                    @endif
                </div>
            </div>
            @if ($attempt->graded_at)
                <p class="mt-4 border-t border-slate-100 pt-3 text-sm text-slate-500">Dinilai {{ $attempt->graded_at->format('d M Y, H:i') }}</p>
            @endif
            @if ($attempt->is_terminated_by_system)
                <p class="mt-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">Pengerjaan ini dihentikan otomatis oleh sistem proctoring.</p>
            @elseif (!empty($attempt->access_restorations))
                <p class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">Guru pernah membuka kembali akses untuk pengerjaan ini.</p>
            @endif
        </header>

        @if ($essayQuestions->isNotEmpty())
            <form method="POST" action="{{ route('teacher.results.grade', $attempt) }}" class="space-y-5">
                @csrf
        @endif

        <section class="space-y-4" aria-label="Jawaban siswa">
            @foreach ($questions as $index => $question)
                @php
                    $studentAnswer = $attempt->answers[(string) $question->id] ?? null;
                    $isCorrect = $question->isMultipleChoice() && $studentAnswer !== null && $studentAnswer === $question->answer_key;
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-sm font-bold text-indigo-800">{{ $index + 1 }}</span>
                            <div>
                                <h2 class="font-semibold text-slate-950">Soal {{ $index + 1 }}</h2>
                                <p class="text-xs font-medium text-slate-500">{{ $question->isEssay() ? 'Esai' : 'Pilihan ganda' }} · Maks. {{ $question->points }} poin</p>
                            </div>
                        </div>
                        @if ($question->isMultipleChoice())
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $isCorrect ? 'bg-emerald-100 text-emerald-900' : 'bg-rose-100 text-rose-900' }}">{{ $isCorrect ? 'Benar' : 'Perlu ditinjau' }}</span>
                        @endif
                    </div>

                    <p class="mt-4 whitespace-pre-line text-base leading-7 text-slate-800">{{ $question->prompt }}</p>

                    @if ($question->isMultipleChoice())
                        @php($options = $question->options ?? [])
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($options as $key => $option)
                                <div class="rounded-lg border px-3.5 py-3 text-sm {{ $key === $question->answer_key ? 'border-emerald-300 bg-emerald-50 text-emerald-950' : ($key === $studentAnswer ? 'border-rose-300 bg-rose-50 text-rose-950' : 'border-slate-200 bg-white text-slate-700') }}">
                                    <span class="font-bold">{{ $key }}.</span> {{ $option }}
                                    @if ($key === $studentAnswer)<span class="ml-1 text-xs font-semibold">Jawaban siswa</span>@endif
                                    @if ($key === $question->answer_key)<span class="ml-1 text-xs font-semibold">Kunci</span>@endif
                                </div>
                            @endforeach
                        </div>
                        @if ($studentAnswer === null)
                            <p class="mt-3 text-sm font-medium text-rose-700">Siswa tidak menjawab soal ini.</p>
                        @endif
                    @else
                        <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Jawaban siswa</p>
                            <p class="whitespace-pre-line text-base leading-7 text-slate-800">{{ $studentAnswer ?: 'Siswa tidak mengisi jawaban.' }}</p>
                        </div>
                        @if ($question->sample_answer)
                            <details class="mt-3 rounded-lg border border-indigo-100">
                                <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-indigo-800">Lihat pedoman jawaban guru</summary>
                                <p class="whitespace-pre-line border-t border-indigo-100 px-4 py-3 text-sm leading-6 text-slate-700">{{ $question->sample_answer }}</p>
                            </details>
                        @endif

                        <div class="mt-4 grid gap-4 sm:grid-cols-[180px_minmax(0,1fr)]">
                            <div>
                                <label for="essay-score-{{ $question->id }}" class="mb-1.5 block text-sm font-semibold text-slate-800">Nilai esai</label>
                                <div class="flex items-center gap-2">
                                    <input id="essay-score-{{ $question->id }}" type="number" name="essay_scores[{{ $question->id }}]" value="{{ old('essay_scores.'.$question->id, $attempt->essay_scores[$question->id] ?? '') }}" min="0" max="{{ $question->points }}" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base">
                                    <span class="shrink-0 text-sm text-slate-500">/ {{ $question->points }}</span>
                                </div>
                            </div>
                            <div>
                                <label for="essay-feedback-{{ $question->id }}" class="mb-1.5 block text-sm font-semibold text-slate-800">Catatan untuk siswa <span class="font-normal text-slate-500">(opsional)</span></label>
                                <textarea id="essay-feedback-{{ $question->id }}" name="essay_feedback[{{ $question->id }}]" rows="2" maxlength="1000" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Tulis umpan balik singkat">{{ old('essay_feedback.'.$question->id, $attempt->essay_feedback[$question->id] ?? '') }}</textarea>
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>

        @if ($essayQuestions->isNotEmpty())
                <div class="sticky bottom-3 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white/95 p-4 shadow-lg backdrop-blur sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600">Nilai akhir dihitung dari total poin PG dan esai.</p>
                    <button type="submit" class="rounded-lg bg-indigo-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-800">{{ $attempt->graded_at ? 'Simpan perubahan nilai' : 'Simpan penilaian' }}</button>
                </div>
            </form>
        @endif
    </div>
@endsection
