@extends('layouts.portal')

@section('title', 'Hasil '.$exam->title.' | RuangUji')

@section('content')
    <a href="{{ route('student.exams') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-700 hover:text-indigo-900">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Kembali ke ujian saya
    </a>

    @php
        $score = $attempt->score;
        $grade = $score === null ? null : match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default      => 'E',
        };
        $gradeColor = match($grade) {
            'A' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
            'B' => 'text-teal-700 bg-teal-50 border-teal-200',
            'C' => 'text-amber-700 bg-amber-50 border-amber-200',
            'D' => 'text-orange-700 bg-orange-50 border-orange-200',
            default => 'text-rose-700 bg-rose-50 border-rose-200',
        };
        $scoreColor = match($grade) {
            'A' => 'text-emerald-700',
            'B' => 'text-teal-700',
            'C' => 'text-amber-700',
            'D' => 'text-orange-700',
            default => 'text-rose-700',
        };
        $motivation = $score === null ? 'Jawaban esai Anda sudah dikumpulkan dan menunggu penilaian dari guru.' : match($grade) {
            'A' => 'Luar biasa! Hasil yang sangat memuaskan. Pertahankan prestasi ini! 🎉',
            'B' => 'Bagus sekali! Kamu sudah menguasai sebagian besar materi. Tingkatkan terus! 👍',
            'C' => 'Cukup baik! Masih ada ruang untuk berkembang. Pelajari kembali materi yang belum dipahami.',
            'D' => 'Kamu sudah berusaha. Coba pelajari ulang materi dan diskusikan dengan guru.',
            default => 'Jangan menyerah! Belajar lebih giat dan minta bimbingan guru untuk materi yang sulit.',
        };
        $durationSeconds = max(0, (int) ($attempt->duration_seconds ?? ($attempt->submitted_at->getTimestamp() - $attempt->started_at->getTimestamp())));
        $durationMinutes = intdiv($durationSeconds, 60);
        $remainingSeconds = $durationSeconds % 60;
        $durationText = $durationMinutes > 0
            ? $durationMinutes.' menit'.($remainingSeconds > 0 ? ' '.$remainingSeconds.' detik' : '')
            : $durationSeconds.' detik';
    @endphp

    <section class="mt-5 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        {{-- Header gradient --}}
        <div class="bg-gradient-to-br from-indigo-700 to-indigo-900 px-6 py-8 text-center sm:px-10 sm:py-10">
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-200">{{ $exam->subject->name }}</p>
            <h1 class="mt-2 text-2xl font-bold text-white">{{ $exam->title }}</h1>
        </div>

        {{-- Score --}}
        <div class="px-6 py-8 text-center sm:px-10">
            @if ($score === null)
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Status penilaian</p>
                <h2 class="mt-3 text-2xl font-bold text-indigo-800">Menunggu penilaian guru</h2>
                <p class="mx-auto mt-3 max-w-md text-base leading-7 text-slate-600">{{ $motivation }}</p>
            @else
                <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">Nilai Anda</p>
                <p class="mt-2 text-7xl font-extrabold tracking-tight {{ $scoreColor }}">{{ $score }}<span class="text-3xl font-semibold text-slate-400">/100</span></p>
                <div class="mt-5 inline-flex items-center gap-3 rounded-2xl border px-6 py-3 {{ $gradeColor }}">
                    <span class="text-4xl font-extrabold">{{ $grade }}</span>
                    <div class="text-left">
                        <p class="text-sm font-bold">Grade {{ $grade }}</p>
                        <p class="text-xs opacity-80">{{ match($grade) { 'A' => 'Sangat Baik', 'B' => 'Baik', 'C' => 'Cukup', 'D' => 'Perlu Perbaikan', default => 'Kurang' } }}</p>
                    </div>
                </div>
                <p class="mx-auto mt-5 max-w-md text-base leading-7 text-slate-600">{{ $motivation }}</p>
                <div class="mx-auto mt-6 max-w-sm">
                    <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full transition-[width] duration-700 {{ match($grade) { 'A' => 'bg-emerald-500', 'B' => 'bg-teal-500', 'C' => 'bg-amber-400', 'D' => 'bg-orange-400', default => 'bg-rose-500' } }}" style="width: {{ $score }}%"></div>
                    </div>
                    <p class="mt-1 text-xs text-slate-400">{{ $score }}% dari nilai maksimum</p>
                </div>
            @endif

            @if ($attempt->is_terminated_by_system)
                <div class="mx-auto mt-4 max-w-md rounded-xl border border-rose-200 bg-rose-50 p-4 text-center">
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Peringatan Sistem</p>
                    <p class="mt-1 text-sm font-semibold text-rose-900">
                        @if (!empty($attempt->access_restorations))
                            Ujian ini sempat dihentikan otomatis. Guru kemudian membuka akses kembali; riwayat pelanggaran tetap tercatat.
                        @else
                            Ujian ini diselesaikan otomatis oleh sistem karena mencapai batas toleransi pelanggaran proctoring ({{ count($attempt->violation_logs ?? []) }} pelanggaran tercatat).
                        @endif
                    </p>
                </div>
            @elseif (!empty($attempt->access_restorations))
                <div class="mx-auto mt-4 max-w-md rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-center">
                    <p class="text-sm font-semibold text-emerald-900">Guru membuka kembali akses ujian Anda setelah penghentian otomatis. Riwayat pelanggaran tetap tersimpan.</p>
                </div>
            @endif

            {{-- Meta info --}}
            <div class="mx-auto mt-6 grid max-w-2xl gap-3 sm:grid-cols-3">
                <div class="rounded-xl bg-slate-50 px-4 py-3 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu pengerjaan</p>
                    <p class="mt-1 text-lg font-bold text-slate-800">{{ $durationText }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu mulai</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ $attempt->started_at->format('d M Y') }}</p>
                    <p class="text-xs text-slate-500">{{ $attempt->started_at->format('H:i:s') }} WIB</p>
                </div>
                <div class="rounded-xl bg-slate-50 px-4 py-3 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dikumpulkan</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">{{ $attempt->submitted_at->format('d M Y') }}</p>
                    <p class="text-xs text-slate-500">{{ $attempt->submitted_at->format('H:i:s') }} WIB</p>
                </div>
            </div>

            @if ($attempt->graded_at !== null && $essayQuestions->isNotEmpty())
                <section class="mx-auto mt-7 max-w-2xl border-t border-slate-100 pt-6 text-left" aria-labelledby="essay-feedback-heading">
                    <h2 id="essay-feedback-heading" class="text-lg font-semibold text-slate-950">Hasil esai dan catatan guru</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($essayQuestions as $index => $question)
                            <article class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-sm font-semibold text-slate-900">Esai {{ $index + 1 }}</h3>
                                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $question->prompt }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-bold text-indigo-900">{{ $attempt->essay_scores[$question->id] ?? 0 }}/{{ $question->points }}</span>
                                </div>
                                @if (!empty($attempt->essay_feedback[$question->id]))
                                    <p class="mt-3 rounded-lg bg-slate-50 px-3 py-2.5 text-sm leading-6 text-slate-700">{{ $attempt->essay_feedback[$question->id] }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @else
                <p class="mt-5 text-xs text-slate-400">{{ $essayQuestions->isNotEmpty() ? 'Nilai dan catatan esai akan muncul setelah guru selesai menilai.' : 'Jawaban dan kunci soal tidak ditampilkan. Hubungi guru jika membutuhkan pembahasan.' }}</p>
            @endif

            {{-- Actions --}}
            <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('student.dashboard') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6"/></svg>
                    Kembali ke dashboard
                </a>
                <a href="{{ route('student.exams') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-800">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
                    Lihat ujian lain
                </a>
            </div>
        </div>
    </section>
@endsection
