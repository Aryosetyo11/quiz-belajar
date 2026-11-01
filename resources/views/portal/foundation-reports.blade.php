@extends('layouts.portal')

@section('title', 'Laporan Pembelajaran | Yayasan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Evaluasi pembelajaran</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Laporan ujian</h1>
                <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Bandingkan aktivitas dan hasil ujian antar sekolah sesuai periode yang dipilih.</p>
            </div>
            <a href="{{ route('foundation.reports.export', array_filter($filters, fn ($value) => $value !== null)) }}" class="inline-flex w-fit items-center justify-center rounded-xl border border-teal-300 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-900 transition hover:bg-teal-100">Unduh CSV</a>
        </header>

        <form method="GET" action="{{ route('foundation.reports') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-label="Filter laporan ujian" data-reveal>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1.2fr)_minmax(160px,0.8fr)_minmax(160px,0.8fr)_auto_auto] xl:items-end">
                <label class="text-sm font-semibold text-slate-700">Sekolah
                    <select name="school_id" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">Semua sekolah</option>
                        @foreach ($reportSchools as $school)
                            <option value="{{ $school->id }}" @selected($filters['school_id'] === $school->id)>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm font-semibold text-slate-700">Dari tanggal
                    <input type="date" name="from" value="{{ $filters['from'] }}" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                </label>
                <label class="text-sm font-semibold text-slate-700">Sampai tanggal
                    <input type="date" name="to" value="{{ $filters['to'] }}" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                </label>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Terapkan</button>
                <a href="{{ route('foundation.reports') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
            </div>
            <p class="mt-3 text-xs text-slate-500">Periode mengikuti tanggal jawaban dikumpulkan. Rombel dan jumlah akun mencerminkan kondisi sekolah saat ini.</p>
        </form>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan laporan">
            @foreach ([['Sesi ujian dikerjakan', $metrics->exam_sessions_count, 'Sesi dengan jawaban terkumpul', 'indigo'], ['Jawaban terkumpul', $metrics->submitted_attempts_count, 'Total pengumpulan pada filter', 'teal'], ['Siswa berpartisipasi', $metrics->participating_students_count, 'Siswa unik pada filter', 'amber']] as [$label, $value, $description, $color])
                <article @class(['rounded-2xl border p-5 shadow-sm', 'border-indigo-100 bg-indigo-50/70' => $color === 'indigo', 'border-teal-100 bg-teal-50/70' => $color === 'teal', 'border-amber-100 bg-amber-50/70' => $color === 'amber'])>
                    <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
                    <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($value, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
                </article>
            @endforeach
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-semibold text-slate-700">Rata-rata nilai</p>
                <p class="mt-3 text-3xl font-bold tabular-nums text-slate-950">{{ $metrics->average_score !== null ? number_format((float) $metrics->average_score, 1, ',', '.') : '—' }}</p>
                <p class="mt-1 text-sm text-slate-600">Dari seluruh jawaban bernilai</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="school-comparison-title" data-reveal>
            <div class="border-b border-slate-100 p-5 sm:p-6">
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-700">Perbandingan institusi</p>
                <h2 id="school-comparison-title" class="mt-1 text-xl font-bold text-slate-950">Aktivitas per sekolah</h2>
                <p class="mt-1 text-sm text-slate-600">Gunakan rata-rata nilai sebagai ringkasan; buka detail sekolah untuk melihat sesi dan rombel.</p>
            </div>
            @if ($schools->isEmpty())
                <div class="p-8 text-center">
                    <p class="font-semibold text-slate-900">Belum ada data pada filter ini</p>
                    <p class="mt-1 text-sm text-slate-600">Pilih periode lain atau pastikan sudah ada ujian yang dikumpulkan.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1050px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4 font-semibold">Sekolah</th><th class="px-4 py-4 text-right font-semibold">Siswa</th><th class="px-4 py-4 text-right font-semibold">Rombel</th><th class="px-4 py-4 text-right font-semibold">Sesi</th><th class="px-4 py-4 text-right font-semibold">Terkumpul</th><th class="px-4 py-4 text-right font-semibold">Siswa aktif</th><th class="px-4 py-4 text-right font-semibold">Rata-rata</th><th class="px-5 py-4 text-right font-semibold">Detail</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($schools as $school)
                                @php($examReport = $school->exam_report)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4"><span class="block font-bold text-slate-950">{{ $school->name }}</span><span class="text-xs text-slate-500">{{ $school->code }}</span></td>
                                    <td class="px-4 py-4 text-right tabular-nums text-slate-700">{{ number_format($school->students_count, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right tabular-nums text-slate-700">{{ number_format($school->classrooms_count, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right tabular-nums text-slate-700">{{ number_format($examReport->exam_sessions_count ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right font-semibold tabular-nums text-slate-900">{{ number_format($examReport->submitted_attempts_count ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right tabular-nums text-slate-700">{{ number_format($examReport->participating_students_count ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right font-semibold tabular-nums text-slate-900">{{ $examReport?->average_score !== null ? number_format((float) $examReport->average_score, 1, ',', '.') : '—' }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('foundation.schools.show', array_merge(['school' => $school->id], array_filter($filters, fn ($value) => $value !== null))) }}" class="font-semibold text-indigo-700 underline-offset-4 hover:underline">Lihat sekolah &rarr;</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
