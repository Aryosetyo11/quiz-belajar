@extends('layouts.portal')

@section('title', 'Laporan '.$school->name.' | Yayasan')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
            <div>
                <a href="{{ route('foundation.dashboard', array_filter($filters, fn ($value) => $value !== null)) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 hover:text-indigo-900"><span aria-hidden="true">&larr;</span> Kembali ke ringkasan yayasan</a>
                <p class="mt-5 text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Laporan sekolah</p>
                <div class="mt-1 flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $school->name }}</h1>
                    <span @class(['rounded-full px-3 py-1 text-sm font-semibold', 'bg-emerald-100 text-emerald-800' => $school->is_active, 'bg-slate-100 text-slate-600' => ! $school->is_active])>{{ $school->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
                <p class="mt-2 text-base text-slate-600">Kode {{ $school->code }}@if ($school->address) <span aria-hidden="true">·</span> {{ $school->address }}@endif</p>
            </div>
            <a href="{{ route('foundation.reports.export', array_filter(['school_id' => $school->id, 'from' => $filters['from'], 'to' => $filters['to']], fn ($value) => $value !== null)) }}" class="inline-flex shrink-0 items-center justify-center rounded-xl border border-teal-300 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-900 transition hover:bg-teal-100">Unduh laporan CSV</a>
        </div>

        <form method="GET" action="{{ route('foundation.schools.show', $school) }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-950">Periode aktivitas ujian</h2>
                    <p class="mt-1 text-sm text-slate-600">Tanggal mengacu pada saat siswa mengumpulkan jawaban.</p>
                </div>
                <div class="grid gap-3 sm:grid-cols-[1fr_1fr_auto_auto] sm:items-end">
                    <label class="text-sm font-semibold text-slate-700">Dari tanggal
                        <input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                    </label>
                    <label class="text-sm font-semibold text-slate-700">Sampai tanggal
                        <input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                    </label>
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Terapkan</button>
                    <a href="{{ route('foundation.schools.show', $school) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
                </div>
            </div>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan sekolah">
            @foreach ([['Siswa', $school->students_count, 'Akun di sekolah saat ini'], ['Guru', $school->teachers_count, 'Akun di sekolah saat ini'], ['Rombel', $classrooms->count(), 'Kelompok belajar terdaftar'], ['Mapel', $school->subjects_count, 'Mapel lintas rombel']] as [$label, $value, $description])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-reveal>
                    <p class="text-sm font-semibold text-slate-600">{{ $label }}</p>
                    <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($value, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Aktivitas ujian sekolah">
            <article class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5">
                <p class="text-sm font-semibold text-indigo-900">Sesi dengan pengerjaan</p>
                <p class="mt-3 text-3xl font-bold tabular-nums text-indigo-950">{{ number_format($metrics->exam_sessions_count, 0, ',', '.') }}</p>
            </article>
            <article class="rounded-2xl border border-teal-100 bg-teal-50/70 p-5">
                <p class="text-sm font-semibold text-teal-900">Jawaban terkumpul</p>
                <p class="mt-3 text-3xl font-bold tabular-nums text-teal-950">{{ number_format($metrics->submitted_attempts_count, 0, ',', '.') }}</p>
            </article>
            <article class="rounded-2xl border border-amber-100 bg-amber-50/70 p-5">
                <p class="text-sm font-semibold text-amber-900">Siswa yang berpartisipasi</p>
                <p class="mt-3 text-3xl font-bold tabular-nums text-amber-950">{{ number_format($metrics->participating_students_count, 0, ',', '.') }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-600">Rata-rata nilai</p>
                <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ $metrics->average_score !== null ? number_format((float) $metrics->average_score, 1, ',', '.') : '—' }}</p>
                <p class="mt-1 text-sm text-slate-500">Dari pengerjaan yang sudah terkumpul</p>
            </article>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,0.9fr)_minmax(0,1.4fr)]">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Struktur belajar</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-950">Rombel sekolah</h2>
                    <p class="mt-1 text-sm text-slate-600">Jumlah siswa dan mapel pada setiap rombel.</p>
                </div>
                @if ($classrooms->isEmpty())
                    <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600">Belum ada rombel untuk sekolah ini.</div>
                @else
                    <div class="mt-5 space-y-3">
                        @foreach ($classrooms as $classroom)
                            <article class="rounded-xl border border-slate-200 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div><h3 class="font-bold text-slate-950">{{ $classroom->name }}</h3><p class="mt-1 text-sm text-slate-600">{{ $classroom->level ?: 'Rombel' }} <span aria-hidden="true">·</span> {{ $classroom->academic_year }}</p></div>
                                    <span class="shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $classroom->students_count }} siswa</span>
                                </div>
                                <p class="mt-3 text-sm font-semibold text-slate-700">{{ $classroom->subjects_count }} mapel</p>
                                @if ($classroom->subjects->isNotEmpty())
                                    <ul class="mt-2 space-y-1 text-sm text-slate-600">
                                        @foreach ($classroom->subjects as $subject)
                                            <li class="flex justify-between gap-3"><span>{{ $subject->name }}</span><span class="text-right text-slate-500">{{ $subject->teacher?->name ?? 'Pengajar belum ditentukan' }}</span></li>
                                        @endforeach
                                    </ul>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-700">Rekap pembelajaran</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-950">Hasil ujian</h2>
                    <p class="mt-1 text-sm text-slate-600">Sesi yang memiliki jawaban terkumpul pada periode terpilih.</p>
                </div>
                @if ($exams->isEmpty())
                    <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                        <p class="font-semibold text-slate-900">Belum ada pengerjaan ujian pada periode ini</p>
                        <p class="mt-1 text-sm text-slate-600">Ubah rentang tanggal atau periksa kembali aktivitas ujian sekolah.</p>
                    </div>
                @else
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[680px] text-left text-sm">
                            <thead class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Sesi / mapel</th><th class="pb-3 font-semibold">Rombel & pengajar</th><th class="pb-3 text-right font-semibold">Terkumpul</th><th class="pb-3 text-right font-semibold">Rata-rata</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($exams as $exam)
                                    <tr>
                                        <td class="py-3"><span class="block font-semibold text-slate-900">{{ $exam->title }}</span><span class="text-xs text-slate-500">{{ $exam->subject?->name }} · {{ $exam->starts_at?->format('d M Y') ?? 'Tanpa tanggal' }}</span></td>
                                        <td class="py-3 text-slate-600"><span class="block">{{ $exam->subject?->classroom?->name ?? 'Rombel tidak tersedia' }}</span><span class="text-xs text-slate-500">{{ $exam->subject?->teacher?->name ?? 'Pengajar tidak tersedia' }}</span></td>
                                        <td class="py-3 text-right font-semibold tabular-nums text-slate-800">{{ number_format($exam->submitted_attempts_count, 0, ',', '.') }}</td>
                                        <td class="py-3 text-right font-semibold tabular-nums text-slate-800">{{ $exam->average_score !== null ? number_format((float) $exam->average_score, 1, ',', '.') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>
        </section>
    </div>
@endsection
