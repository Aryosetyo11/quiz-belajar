@extends('layouts.portal')

@section('title', $exam->title.' | RuangUji')

@section('content')
{{-- Warning overlay --}}
<div data-warning-overlay class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="mx-4 max-w-md rounded-2xl border border-rose-200 bg-white p-6 shadow-xl">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </span>
            <div>
                <h3 class="text-base font-bold text-slate-900">Pelanggaran terdeteksi!</h3>
                <p data-warning-message class="mt-1 text-sm text-slate-600">Aktivitas mencurigakan terdeteksi.</p>
            </div>
        </div>
        <button type="button" data-warning-dismiss class="mt-5 w-full rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">Saya mengerti, kembali ke ujian</button>
    </div>
</div>

{{-- Lock the exam until fullscreen is restored. --}}
<div data-fullscreen-lock class="fixed inset-0 z-[80] hidden items-center justify-center bg-slate-950/90 p-6 backdrop-blur-sm">
    <section class="w-full max-w-md rounded-2xl bg-white p-7 text-center shadow-2xl">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-700">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 4h.008M4.93 19h14.14a2 2 0 0 0 1.73-3L13.73 3.75a2 2 0 0 0-3.46 0L3.2 16a2 2 0 0 0 1.73 3Z"/></svg>
        </span>
        <h2 class="mt-4 text-xl font-bold text-slate-950">Ujian dijeda</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Anda keluar dari layar penuh. Sistem mencoba mengaktifkannya kembali. Jika browser menolak, tekan tombol di bawah untuk melanjutkan.</p>
        <button type="button" data-fullscreen-resume class="mt-6 w-full rounded-xl bg-indigo-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">Kembali ke layar penuh</button>
    </section>
</div>

{{-- Confirm submit dialog --}}
<div data-confirm-dialog class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm">
    <div class="mx-4 max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
        <h3 class="text-base font-bold text-slate-900">Konfirmasi pengumpulan</h3>
        <p class="mt-2 text-sm text-slate-600">Masih ada <strong data-unanswered-count class="text-rose-700">0</strong> soal yang belum dijawab. Yakin ingin mengumpulkan?</p>
        <div class="mt-5 flex gap-3">
            <button type="button" data-confirm-cancel class="flex-1 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali</button>
            <button type="button" data-confirm-proceed class="flex-1 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Ya, kumpulkan</button>
        </div>
    </div>
</div>

{{-- Exam header bar --}}
<div class="sticky top-0 z-30 -mx-5 -mt-8 border-b border-slate-200 bg-white px-5 py-3 sm:-mx-8 sm:px-8 xl:-mx-10 xl:px-10"
     data-exam-proctoring
     data-enable-proctoring="{{ $exam->enable_proctoring ? '1' : '0' }}"
     data-max-violations="{{ $exam->max_violations ?? 3 }}"
     data-current-violations="{{ $attempt->violation_count ?? 0 }}"
     data-started-at-timestamp="{{ $attempt->started_at->timestamp }}"
     data-log-violation-url="{{ route('student.exams.log-violation', $exam) }}"
     data-autosave-url="{{ route('student.exams.autosave', $exam) }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase text-indigo-700">{{ $exam->subject->name }}</p>
            <h1 class="mt-0.5 truncate text-base font-bold text-slate-950">{{ $exam->title }}</h1>
        </div>
        <div class="flex items-center gap-4">
            <button type="button" data-request-fullscreen class="hidden rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100">
                Masuk layar penuh
            </button>
            {{-- Violation badge --}}
            @if ($exam->enable_proctoring)
                <span data-violation-badge class="flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-700">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.948 3.374H4.645c-1.73 0-2.813-1.874-1.948-3.374l7.048-12.21a2.25 2.25 0 0 1 3.93 0l7.028 12.21Zm-9.303.376h.008v.008H12v-.008Z"/></svg>
                    Pelanggaran: <span data-violation-count>{{ $attempt->violation_count ?? 0 }}</span>/{{ $exam->max_violations ?? 3 }}
                </span>
            @endif
            {{-- Timer --}}
            @php($deadline = $attempt->started_at->copy()->addMinutes($exam->duration_minutes + (int) $attempt->extra_time_minutes))
            @if (empty($attempt->access_restorations) && $exam->ends_at && $exam->ends_at->lt($deadline)) @php($deadline = $exam->ends_at) @endif
            @php($remainingSeconds = max(0, $deadline->timestamp - now()->timestamp))
            @php($totalDurationSeconds = max(1, $deadline->timestamp - $attempt->started_at->timestamp))
            <div class="min-w-44 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-amber-950 transition-colors" data-exam-countdown data-remaining-seconds="{{ $remainingSeconds }}" data-total-seconds="{{ $totalDurationSeconds }}" role="timer" aria-label="Waktu tersisa">
                <div class="flex items-center justify-between gap-3">
                    <span class="text-xs font-semibold text-amber-800">Waktu tersisa</span>
                    <time class="font-mono text-lg font-bold tabular-nums" data-countdown-output>--:--</time>
                </div>
                <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-white/80" aria-hidden="true">
                    <span data-countdown-progress class="block h-full w-full rounded-full bg-amber-500 transition-[width,background-color] duration-500"></span>
                </div>
                <p data-countdown-status class="mt-1 text-right text-[11px] font-medium text-amber-800">Waktu berjalan</p>
            </div>
        </div>
    </div>
</div>

@if ($errors->any())<div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">{{ $errors->first() }}</div>@endif

<div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_240px]">
    {{-- Question area --}}
    <div>
        <form method="POST" action="{{ route('student.exams.submit', $exam) }}" data-exam-submit id="exam-form">
            @csrf
            <input type="hidden" name="elapsed_seconds" value="0" data-exam-elapsed>
            @foreach ($exam->questions as $index => $question)
                <div data-question-card="{{ $index }}" class="{{ $index !== 0 ? 'hidden' : '' }} rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    {{-- Question header --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-xs font-bold text-indigo-700">{{ $index + 1 }}</span>
                            <span class="text-xs font-semibold text-slate-500">{{ $question->points }} poin</span>
                        </div>
                        <button
                            type="button"
                            data-flag-question="{{ $index }}"
                            class="shrink-0 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-amber-100 hover:text-amber-800"
                        >Tandai</button>
                    </div>

                    {{-- Question text --}}
                    <p class="mt-4 whitespace-pre-line text-base leading-7 text-slate-800">{{ $question->prompt }}</p>

                    {{-- Answer field --}}
                    @if ($question->isEssay())
                        <div class="mt-5">
                            <label for="answer-{{ $question->id }}" class="mb-2 block text-sm font-semibold text-slate-800">Jawaban esai</label>
                            <textarea id="answer-{{ $question->id }}" name="answers[{{ $question->id }}]" rows="7" maxlength="5000" class="block w-full rounded-xl border border-slate-300 px-4 py-3 text-base leading-7 text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100" placeholder="Tuliskan jawaban Anda di sini...">{{ old('answers.'.$question->id, $attempt->answers[$question->id] ?? '') }}</textarea>
                        </div>
                    @else
                        <div class="mt-5 grid gap-2.5 sm:grid-cols-2">
                            @foreach ($question->options as $key => $option)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 transition hover:border-indigo-300 hover:bg-indigo-50/60 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 has-[:checked]:font-semibold has-[:checked]:text-indigo-900">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $key }}" @checked(old('answers.'.$question->id, $attempt->answers[$question->id] ?? null) === $key) class="mt-0.5 h-4 w-4 border-slate-300 text-indigo-700">
                                    <span><strong class="mr-1 text-indigo-700">{{ $key }}.</strong>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    {{-- Navigation buttons --}}
                    <div class="mt-6 flex items-center justify-between gap-4 border-t border-slate-100 pt-5">
                        <button type="button" data-prev-question class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-40">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                            Sebelumnya
                        </button>
                        <span class="text-xs text-slate-500">{{ $index + 1 }} / {{ $exam->questions->count() }}</span>
                        @if ($index < $exam->questions->count() - 1)
                            <button type="button" data-next-question class="inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">
                                Berikutnya
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                            </button>
                        @else
                            <button type="button" data-submit-btn class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Kumpulkan jawaban
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </form>

        <p data-answer-save-status class="mt-3 text-sm text-slate-500" role="status" aria-live="polite">Jawaban disimpan otomatis saat Anda menjawab.</p>

        {{-- Warning bar --}}
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <p class="text-sm font-semibold text-amber-900">Perhatian!</p>
            <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs text-amber-800">
                <li>Jangan berpindah tab atau mengecilkan jendela browser</li>
                <li>Setelah 3 pelanggaran, ujian dikumpulkan otomatis</li>
                <li>Pilihan dan jawaban esai disimpan otomatis ke server</li>
            </ul>
        </div>
    </div>

    {{-- Question navigation panel --}}
    <aside class="order-first lg:order-last lg:sticky lg:top-24 lg:self-start">
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <h2 class="mb-3 text-sm font-bold text-slate-900">Navigasi soal</h2>
            <div class="grid grid-cols-5 gap-1.5">
                @foreach ($exam->questions as $index => $question)
                    <button
                        type="button"
                        data-nav-question="{{ $index }}"
                        class="flex h-9 w-full items-center justify-center rounded-lg bg-slate-200 text-xs font-bold text-slate-700 transition hover:opacity-80"
                        title="Soal {{ $index + 1 }}"
                    >{{ $index + 1 }}</button>
                @endforeach
            </div>
            <div class="mt-4 space-y-2 text-xs">
                <div class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-slate-200"></span><span class="text-slate-600">Belum dijawab</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-green-500"></span><span class="text-slate-600">Sudah dijawab</span></div>
                <div class="flex items-center gap-2"><span class="h-3 w-3 rounded bg-amber-400"></span><span class="text-slate-600">Ditandai</span></div>
            </div>
            <div class="mt-4 border-t border-slate-100 pt-4">
                <button type="button" data-submit-btn class="w-full rounded-lg bg-emerald-700 px-3 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800">
                    Kumpulkan jawaban
                </button>
            </div>
        </div>
    </aside>
</div>
@endsection
