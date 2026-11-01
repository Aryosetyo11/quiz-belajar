@extends('layouts.portal')

@section('title', 'Data Sekolah | Yayasan')

@section('content')
    <div class="space-y-6">
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Jaringan institusi</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Data sekolah</h1>
                <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Lihat kondisi akun, rombel, mapel, dan aktivitas tiap sekolah yang terhubung.</p>
            </div>
            <span class="inline-flex w-fit rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">{{ number_format($totalSchools, 0, ',', '.') }} sekolah terdaftar</span>
        </header>

        <form method="GET" action="{{ route('foundation.schools') }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row" role="search">
            <label for="school-search" class="sr-only">Cari nama atau kode sekolah</label>
            <input id="school-search" type="search" name="q" value="{{ $search }}" placeholder="Cari nama atau kode sekolah..." class="min-w-0 flex-1 rounded-xl border-slate-300 px-4 py-3 text-sm focus:border-indigo-600 focus:ring-indigo-600">
            <div class="flex gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-xl bg-indigo-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-800 sm:flex-none">Cari sekolah</button>
                @if ($search !== '')
                    <a href="{{ route('foundation.schools') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
                @endif
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-label="Daftar sekolah" data-reveal>
            @if ($schools->isEmpty())
                <div class="p-8 text-center">
                    <p class="font-semibold text-slate-900">{{ $search === '' ? 'Belum ada sekolah terdaftar' : 'Sekolah tidak ditemukan' }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $search === '' ? 'Sekolah yang dibuat admin All Remote akan tampil di sini.' : 'Coba nama atau kode sekolah yang berbeda.' }}</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
                        <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-4 font-semibold">Sekolah</th><th class="px-4 py-4 font-semibold">Status</th><th class="px-4 py-4 text-right font-semibold">Guru</th><th class="px-4 py-4 text-right font-semibold">Siswa</th><th class="px-4 py-4 text-right font-semibold">Rombel</th><th class="px-4 py-4 text-right font-semibold">Mapel</th><th class="px-5 py-4 text-right font-semibold">Detail</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($schools as $school)
                                <tr class="transition hover:bg-slate-50/80">
                                    <td class="px-5 py-4"><span class="block font-bold text-slate-950">{{ $school->name }}</span><span class="mt-0.5 block text-xs text-slate-500">{{ $school->code }}</span></td>
                                    <td class="px-4 py-4"><span @class(['rounded-full px-2.5 py-1 text-xs font-semibold', 'bg-emerald-100 text-emerald-800' => $school->is_active, 'bg-slate-100 text-slate-600' => ! $school->is_active])>{{ $school->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                    <td class="px-4 py-4 text-right tabular-nums text-slate-700">{{ number_format($school->teachers_count, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right font-semibold tabular-nums text-slate-900">{{ number_format($school->students_count, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right tabular-nums text-slate-700">{{ number_format($school->classrooms_count, 0, ',', '.') }}</td>
                                    <td class="px-4 py-4 text-right tabular-nums text-slate-700">{{ number_format($school->subjects_count, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('foundation.schools.show', $school) }}" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 font-semibold text-indigo-700 transition hover:bg-indigo-50">Lihat laporan <span aria-hidden="true">&rarr;</span></a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <p class="text-xs text-slate-500">Halaman ini hanya menampilkan data. Perubahan sekolah, akun, dan rombel dilakukan oleh admin All Remote.</p>
    </div>
@endsection
