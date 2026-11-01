@extends('layouts.portal')

@section('title', 'Ringkasan Yayasan')

@section('content')
    <div class="space-y-7">
        <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between" data-reveal>
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Portal yayasan</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Ringkasan institusi</h1>
                <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Pantau perkembangan sekolah dan aktivitas pembelajaran dalam satu tampilan.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('foundation.schools') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Lihat sekolah</a>
                <a href="{{ route('foundation.reports') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-800">Buka laporan</a>
            </div>
        </header>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan yayasan">
            @foreach ([['Sekolah', $schoolCount, 'Institusi terdaftar', 'bg-indigo-50 text-indigo-800'], ['Siswa', $studentCount, 'Akun pada seluruh sekolah', 'bg-orange-50 text-orange-800'], ['Guru', $teacherCount, 'Pengajar terdaftar', 'bg-teal-50 text-teal-800'], ['Ujian berlangsung', $activeSessionCount, 'Sesi yang sedang aktif', 'bg-emerald-50 text-emerald-800']] as [$label, $value, $description, $color])
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
                    <span class="inline-flex rounded-lg px-2.5 py-1 text-xs font-bold {{ $color }}">{{ $label }}</span>
                    <p class="mt-4 text-3xl font-bold tabular-nums tracking-tight text-slate-950">{{ number_format($value, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.3fr)_minmax(260px,0.7fr)]">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Jaringan sekolah</p>
                        <h2 class="mt-1 text-xl font-bold text-slate-950">Sekolah dengan siswa terbanyak</h2>
                        <p class="mt-1 text-sm text-slate-600">Ringkasan jumlah siswa dan rombel saat ini.</p>
                    </div>
                    <a href="{{ route('foundation.schools') }}" class="text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Semua sekolah &rarr;</a>
                </div>

                @if ($topSchools->isEmpty())
                    <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                        <p class="font-semibold text-slate-900">Belum ada sekolah terdaftar</p>
                        <p class="mt-1 text-sm text-slate-600">Data sekolah akan terlihat setelah admin All Remote menambahkannya.</p>
                    </div>
                @else
                    <div class="mt-5 overflow-x-auto">
                        <table class="w-full min-w-[520px] text-left text-sm">
                            <thead class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Sekolah</th><th class="pb-3 text-right font-semibold">Siswa</th><th class="pb-3 text-right font-semibold">Guru</th><th class="pb-3 text-right font-semibold">Rombel</th></tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($topSchools as $school)
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3"><a href="{{ route('foundation.schools.show', $school) }}" class="font-semibold text-indigo-700 underline-offset-4 hover:underline">{{ $school->name }}</a><span class="block text-xs text-slate-500">{{ $school->code }}</span></td>
                                        <td class="py-3 text-right font-semibold tabular-nums text-slate-800">{{ number_format($school->students_count, 0, ',', '.') }}</td>
                                        <td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($school->teachers_count, 0, ',', '.') }}</td>
                                        <td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($school->classrooms_count, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>

            <aside class="rounded-2xl border border-indigo-100 bg-indigo-50/70 p-5 sm:p-6" data-reveal>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-800">Akses cepat</p>
                <h2 class="mt-1 text-xl font-bold text-slate-950">Apa yang ingin ditinjau?</h2>
                <div class="mt-5 space-y-3">
                    <a href="{{ route('foundation.schools') }}" class="flex items-center justify-between gap-3 rounded-xl border border-white bg-white p-4 font-semibold text-slate-800 shadow-sm transition hover:border-indigo-200 hover:text-indigo-800"><span>Direktori sekolah</span><span aria-hidden="true">&rarr;</span></a>
                    <a href="{{ route('foundation.reports') }}" class="flex items-center justify-between gap-3 rounded-xl border border-white bg-white p-4 font-semibold text-slate-800 shadow-sm transition hover:border-indigo-200 hover:text-indigo-800"><span>Perbandingan hasil ujian</span><span aria-hidden="true">&rarr;</span></a>
                </div>
                <p class="mt-5 text-sm leading-6 text-indigo-950/75">Angka siswa, guru, dan rombel menggambarkan kondisi akun saat ini. Aktivitas ujian dan nilai dapat ditinjau berdasarkan periode di halaman laporan.</p>
            </aside>
        </section>

        <p class="text-xs text-slate-500">Ringkasan diperbarui saat halaman dibuka. Data mengikuti aktivitas yang tercatat di portal.</p>
    </div>
@endsection
