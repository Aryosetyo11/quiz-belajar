@extends('layouts.portal')

@section('title', $isFoundationAdmin ? 'Dashboard Yayasan' : 'Dashboard All Remote')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">{{ $isFoundationAdmin ? 'Dashboard yayasan' : 'Dashboard All Remote' }}</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Halo, {{ auth()->user()->name }} 👋</h1>
        <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">{{ $isFoundationAdmin ? 'Lihat gambaran jumlah siswa, guru, dan aktivitas pembelajaran di seluruh institusi.' : 'Kelola akun, penugasan kelas, dan bank soal serta pantau aktivitas portal.' }}</p>
    </div>
    @if (! $isFoundationAdmin)
        <div class="flex shrink-0 flex-wrap gap-2">
            <a href="{{ route('admin.users') }}#buat-akun" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">Tambah akun <span aria-hidden="true">&rarr;</span></a>
            <a href="{{ route('admin.schools') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Sekolah & rombel</a>
            <a href="{{ route('admin.exam-sessions') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Sesi ujian</a>
        </div>
    @endif
</div>

@if ($isFoundationAdmin)
    <form method="GET" action="{{ route('foundation.dashboard') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-label="Filter laporan yayasan" data-reveal>
        <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Laporan yayasan</p>
                <h2 class="mt-1 text-xl font-bold text-slate-950">Aktivitas ujian per periode</h2>
                <p class="mt-1 text-sm text-slate-600">Pilih sekolah dan tanggal pengumpulan untuk menyaring angka ujian dan tabel sekolah.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[720px]">
                <label class="text-sm font-semibold text-slate-700">Sekolah
                    <select name="school_id" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">Semua sekolah</option>
                        @foreach ($reportSchools as $school)
                            <option value="{{ $school->id }}" @selected($reportFilters['school_id'] === $school->id)>{{ $school->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm font-semibold text-slate-700">Dari tanggal
                    <input type="date" name="from" value="{{ $reportFilters['from'] }}" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                </label>
                <label class="text-sm font-semibold text-slate-700">Sampai tanggal
                    <input type="date" name="to" value="{{ $reportFilters['to'] }}" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                </label>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Terapkan filter</button>
                <a href="{{ route('foundation.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
                <a href="{{ route('foundation.reports.export', array_filter($reportFilters, fn ($value) => $value !== null)) }}" class="inline-flex items-center justify-center rounded-xl border border-teal-300 bg-teal-50 px-4 py-2.5 text-sm font-semibold text-teal-900 transition hover:bg-teal-100">Unduh CSV</a>
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Filter tanggal mengikuti waktu jawaban ujian dikumpulkan. Data jumlah akun, rombel, dan mapel menunjukkan kondisi saat ini.</p>
    </form>
@endif

@unless ($isFoundationAdmin)
    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="operations-title" data-reveal>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Operasional hari ini</p>
                <h2 id="operations-title" class="mt-1 text-xl font-bold text-slate-950">Status portal dan ujian</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Pantau sesi yang perlu diperhatikan dan tindak lanjut pengajar.</p>
            </div>
            <span class="text-sm font-medium text-slate-500">Diperbarui {{ now()->translatedFormat('d M Y, H:i') }}</span>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4">
                <p class="text-sm font-semibold text-emerald-900">Ujian berlangsung</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-emerald-950">{{ number_format($activeSessionCount, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm text-emerald-800">Sesi terbit pada jadwal aktif</p>
            </article>
            <article class="rounded-xl border border-blue-200 bg-blue-50/70 p-4">
                <p class="text-sm font-semibold text-blue-900">Ujian terjadwal</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-blue-950">{{ number_format($upcomingSessionCount, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm text-blue-800">Sesi terbit yang belum dimulai</p>
            </article>
            <article class="rounded-xl border border-amber-200 bg-amber-50/70 p-4">
                <p class="text-sm font-semibold text-amber-900">Menunggu nilai esai</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-amber-950">{{ number_format($pendingEssayReviewCount, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm text-amber-800">Pengajar perlu menyelesaikan penilaian</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-800">Sesi masih berupa draf</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-slate-950">{{ number_format($draftSessionCount, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm text-slate-600">Belum dapat diakses siswa</p>
            </article>
        </div>

        <div class="mt-6 border-t border-slate-100 pt-5">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h3 class="text-lg font-bold text-slate-950">Sesi ujian terbaru</h3>
                    <p class="mt-1 text-sm text-slate-600">Status publikasi, jadwal, dan jumlah pengerjaan.</p>
                </div>
                <span class="text-sm font-medium text-slate-500">Ringkasan lintas sesi</span>
            </div>

            @if ($recentExamSessions->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-center">
                    <p class="font-semibold text-slate-900">Belum ada sesi ujian</p>
                    <p class="mt-1 text-sm text-slate-600">Sesi ujian yang dibuat pengajar akan muncul di sini.</p>
                </div>
            @else
                <div class="mt-4 space-y-3">
                    @foreach ($recentExamSessions as $exam)
                        @php
                            $examStatus = ! $exam->is_published
                                ? 'Draf'
                                : ($exam->starts_at?->isFuture()
                                    ? 'Terjadwal'
                                    : ($exam->ends_at?->isPast() ? 'Selesai' : 'Berlangsung'));
                            $examStatusClass = match ($examStatus) {
                                'Draf' => 'bg-slate-100 text-slate-700',
                                'Terjadwal' => 'bg-blue-100 text-blue-800',
                                'Selesai' => 'bg-violet-100 text-violet-800',
                                default => 'bg-emerald-100 text-emerald-800',
                            };
                        @endphp
                        <article class="flex flex-col gap-3 rounded-xl border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h4 class="truncate font-bold text-slate-950">{{ $exam->title }}</h4>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $examStatusClass }}">{{ $examStatus }}</span>
                                </div>
                                <p class="mt-1 text-sm text-slate-600">{{ $exam->subject->name }} · {{ $exam->starts_at?->format('d M Y, H:i') ?? 'Tanpa jadwal mulai' }}</p>
                            </div>
                            <div class="flex shrink-0 gap-5 text-sm">
                                <p><span class="block text-xs font-medium text-slate-500">Pengerjaan</span><strong class="mt-1 block text-slate-900">{{ number_format($exam->attempts_count, 0, ',', '.') }}</strong></p>
                                <p><span class="block text-xs font-medium text-slate-500">Terkumpul</span><strong class="mt-1 block text-slate-900">{{ number_format($exam->submitted_attempts_count, 0, ',', '.') }}</strong></p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6 border-t border-slate-100 pt-5">
            <div>
                <h3 class="text-lg font-bold text-slate-950">Akun perlu tindak lanjut</h3>
                <p class="mt-1 text-sm text-slate-600">Pastikan guru dan siswa sudah terhubung ke kegiatan belajar.</p>
            </div>
            @if ($studentsWithoutSubjectCount > 0 || $teachersWithoutSubjectCount > 0)
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @if ($studentsWithoutSubjectCount > 0)
                        <article class="flex flex-col gap-3 rounded-xl border border-orange-200 bg-orange-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-orange-950">Siswa belum terdaftar di mata pelajaran</p>
                                <p class="mt-1 text-sm text-orange-900">{{ number_format($studentsWithoutSubjectCount, 0, ',', '.') }} akun perlu dicek agar bisa melihat materi dan ujian.</p>
                            </div>
                            <a href="{{ route('admin.users', ['role' => 'siswa', 'attention' => 'students_without_subject']) }}" class="inline-flex shrink-0 justify-center rounded-lg border border-orange-300 bg-white px-3 py-2 text-sm font-bold text-orange-900 transition hover:bg-orange-100">Lihat siswa</a>
                        </article>
                    @endif
                    @if ($teachersWithoutSubjectCount > 0)
                        <article class="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="font-bold text-amber-950">Guru belum memiliki mata pelajaran</p>
                                <p class="mt-1 text-sm text-amber-900">{{ number_format($teachersWithoutSubjectCount, 0, ',', '.') }} akun belum terhubung ke kelas.</p>
                            </div>
                            <a href="{{ route('admin.users', ['role' => 'guru', 'attention' => 'teachers_without_subject']) }}" class="inline-flex shrink-0 justify-center rounded-lg border border-amber-300 bg-white px-3 py-2 text-sm font-bold text-amber-950 transition hover:bg-amber-100">Lihat guru</a>
                        </article>
                    @endif
                </div>
            @else
                <p class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">Semua akun siswa dan guru sudah terhubung ke mata pelajaran.</p>
            @endif
        </div>
    </section>
@endunless

<section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan institusi">
    @foreach ([['Total siswa', $studentCount, 'bg-orange-100 text-orange-700', '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>'], ['Total guru', $teacherCount, 'bg-teal-100 text-teal-700', '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>'], ['Mata pelajaran', $subjectCount, 'bg-violet-100 text-violet-700', '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>'], ['Pendaftaran kelas', $enrollmentCount, 'bg-amber-100 text-amber-700', '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>']] as [$label, $value, $color, $svg])
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md sm:p-6" data-reveal>
            <div class="flex items-center justify-between"><p class="text-sm font-semibold text-slate-600">{{ $label }}</p><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $color }}" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">{!! $svg !!}</svg></span></div>
            <p class="mt-5 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($value, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">Data terkini dari portal</p>
        </article>
    @endforeach
</section>

<section class="mt-4 grid gap-4 sm:grid-cols-2" aria-label="Jaringan sekolah">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
        <p class="text-sm font-semibold text-slate-600">Sekolah terhubung</p>
        <p class="mt-3 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($schoolCount, 0, ',', '.') }}</p>
        <p class="mt-1 text-sm text-slate-500">Institusi yang terdaftar di portal</p>
    </article>
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
        <p class="text-sm font-semibold text-slate-600">Rombel lintas sekolah</p>
        <p class="mt-3 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($classroomCount, 0, ',', '.') }}</p>
        <p class="mt-1 text-sm text-slate-500">Kelompok belajar berdasarkan tahun ajaran</p>
    </article>
</section>

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" aria-labelledby="school-summary-title" data-reveal>
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Cakupan institusi</p>
            <h2 id="school-summary-title" class="mt-1 text-xl font-bold text-slate-950">Ringkasan per sekolah</h2>
        </div>
        @unless ($isFoundationAdmin)
            <a href="{{ route('admin.schools') }}" class="text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Kelola sekolah &rarr;</a>
        @endunless
    </div>
    @if ($schoolSummaries->isEmpty())
        <p class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-5 text-sm text-slate-600">Belum ada sekolah terdaftar.</p>
    @else
        <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[960px] text-left text-sm">
                <thead class="border-b border-slate-100 text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Sekolah</th><th class="pb-3 text-right font-semibold">Guru</th><th class="pb-3 text-right font-semibold">Siswa</th><th class="pb-3 text-right font-semibold">Rombel</th><th class="pb-3 text-right font-semibold">Mapel</th><th class="pb-3 text-right font-semibold">Sesi ujian</th><th class="pb-3 text-right font-semibold">Terkumpul</th><th class="pb-3 text-right font-semibold">Rata-rata</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($schoolSummaries as $school)
                        @php($examReport = $school->exam_report)
                        <tr class="hover:bg-slate-50"><td class="py-3">@if ($isFoundationAdmin)<a href="{{ route('foundation.schools.show', array_merge(['school' => $school->id], array_filter($reportFilters, fn ($value) => $value !== null))) }}" class="block font-semibold text-indigo-700 underline-offset-4 hover:underline">{{ $school->name }} <span aria-hidden="true">&rarr;</span></a>@else<span class="block font-semibold text-slate-900">{{ $school->name }}</span>@endif<span class="text-xs text-slate-500">{{ $school->code }}</span></td><td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($school->teachers_count, 0, ',', '.') }}</td><td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($school->students_count, 0, ',', '.') }}</td><td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($school->classrooms_count, 0, ',', '.') }}</td><td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($school->subjects_count, 0, ',', '.') }}</td><td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($examReport->exam_sessions_count ?? 0, 0, ',', '.') }}</td><td class="py-3 text-right tabular-nums text-slate-700">{{ number_format($examReport->submitted_attempts_count ?? 0, 0, ',', '.') }}</td><td class="py-3 text-right tabular-nums text-slate-700">{{ $examReport?->average_score !== null ? number_format((float) $examReport->average_score, 1, ',', '.') : '—' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@if ($isFoundationAdmin)
    {{-- Foundation admin: extra stats --}}
    <section class="mt-5 grid gap-4 sm:grid-cols-3" aria-label="Statistik ujian">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
            <p class="text-sm font-semibold text-slate-600">Sesi ujian dikerjakan</p>
            <p class="mt-4 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($examCount, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">Sesi dengan jawaban terkumpul pada filter</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
            <p class="text-sm font-semibold text-slate-600">Total pengerjaan ujian</p>
            <p class="mt-4 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($attemptCount, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">Jawaban terkumpul pada filter</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
            <p class="text-sm font-semibold text-slate-600">Rata-rata nilai keseluruhan</p>
            <p class="mt-4 text-3xl font-bold tracking-tight text-slate-950">{{ $averageScore ?? '—' }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ $averageScore !== null ? 'Dari jawaban terkumpul pada filter' : 'Belum ada data pada filter' }}</p>
        </article>
    </section>
@endif

<section class="mt-7 grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.85fr)]" aria-label="Analitik institusi">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" data-reveal>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.12em] text-teal-700">Komposisi pengguna</p><h2 class="mt-1.5 text-xl font-bold tracking-tight text-slate-950">Komunitas belajar</h2><p class="mt-1 text-sm text-slate-500">Perbandingan akun berdasarkan peran.</p></div>
            <div class="inline-flex w-fit rounded-xl bg-slate-100 p-1" role="group" aria-label="Pilih tampilan grafik">
                <button type="button" data-chart-mode="count" aria-pressed="true" class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-sm">Jumlah</button>
                <button type="button" data-chart-mode="percentage" aria-pressed="false" class="rounded-lg px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900">Persentase</button>
            </div>
        </div>
        <div class="mt-7 grid items-center gap-7 md:grid-cols-[minmax(190px,0.8fr)_minmax(0,1.2fr)]">
            <div data-chart-donut class="relative mx-auto aspect-square w-full max-w-[220px] rounded-full transition-transform duration-300" style="background: {{ $donutBackground }}" role="img" aria-label="Diagram komposisi {{ $totalAccounts }} akun">
                <div class="absolute inset-[20%] flex flex-col items-center justify-center rounded-full bg-white text-center shadow-inner"><span data-chart-total class="text-3xl font-bold tracking-tight text-slate-950">{{ number_format($totalAccounts, 0, ',', '.') }}</span><span data-chart-total-label class="mt-1 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-500">total akun</span></div>
            </div>
            <div class="space-y-5">
                @foreach ($chartData as $item)
                    @php($barWidth = $totalAccounts > 0 ? ($item['count'] / $totalAccounts) * 100 : 0)
                    <div data-chart-item data-label="{{ $item['label'] }}" data-count="{{ $item['count'] }}" data-percentage="{{ $item['percentage'] }}" data-color="{{ $item['color'] }}">
                        <button type="button" data-chart-filter aria-pressed="false" class="mb-2 flex w-full items-center justify-between gap-3 rounded-lg text-left transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600"><span class="flex items-center gap-2.5 text-sm font-semibold text-slate-700"><span class="h-3 w-3 rounded-full" style="background: {{ $item['color'] }}"></span>{{ $item['label'] }}</span><span class="text-sm font-bold tabular-nums text-slate-950"><span data-chart-value>{{ number_format($item['count'], 0, ',', '.') }}</span> <span data-chart-unit>akun</span></span></button>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ $item['label'] }}" aria-valuemin="0" aria-valuemax="{{ $totalAccounts }}" aria-valuenow="{{ $item['count'] }}"><div data-chart-bar class="h-full rounded-full transition-[width] duration-500 ease-out" style="width: {{ $barWidth }}%; background: {{ $item['color'] }}"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4"><p data-chart-summary class="text-sm text-slate-600" aria-live="polite">Menampilkan semua role dalam grafik.</p><button type="button" data-chart-reset class="rounded-lg px-3 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">Tampilkan semua</button></div>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" data-reveal>
        <p class="text-xs font-bold uppercase tracking-[0.12em] text-orange-700">Aktivitas pembelajaran</p><h2 class="mt-1.5 text-xl font-bold tracking-tight text-slate-950">Ekosistem belajar</h2><p class="mt-1 text-sm text-slate-500">Materi dan pengelolaan yang tercatat.</p>
        <div class="mt-6 divide-y divide-slate-100 rounded-xl border border-slate-100 px-4">
            <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm font-medium text-slate-600">Materi belajar</span><strong class="text-xl font-bold tabular-nums text-violet-800">{{ number_format($materialCount, 0, ',', '.') }}</strong></div>
            <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm font-medium text-slate-600">Admin All Remote</span><strong class="text-xl font-bold tabular-nums text-teal-800">{{ number_format($remoteAdminCount, 0, ',', '.') }}</strong></div>
            <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm font-medium text-slate-600">Admin Yayasan</span><strong class="text-xl font-bold tabular-nums text-amber-800">{{ number_format($foundationAdminCount, 0, ',', '.') }}</strong></div>
        </div>
        <div class="mt-5 rounded-xl bg-[#f1f8f5] p-4"><p class="text-sm font-semibold text-teal-950">Statistik terhubung ke database</p><p class="mt-1 text-sm leading-6 text-teal-900/80">Angka diperbarui setiap kali halaman ringkasan dibuka.</p></div>
    </article>
</section>

@if ($isFoundationAdmin && $topTeachers->isNotEmpty())
    <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" data-reveal>
        <p class="text-xs font-bold uppercase tracking-[0.12em] text-teal-700">Performa pengajar</p>
        <h2 class="mt-1.5 text-xl font-bold text-slate-950">Guru dengan siswa terbanyak</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[400px] text-left text-sm">
                <thead class="text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="pb-3 font-semibold">Nama guru</th>
                        <th class="pb-3 text-right font-semibold">Total siswa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($topTeachers as $teacher)
                        <tr class="transition hover:bg-slate-50">
                            <td class="py-3 font-semibold text-slate-900">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-700">{{ mb_substr($teacher->name, 0, 1) }}</span>
                                    {{ $teacher->name }}
                                </div>
                            </td>
                            <td class="py-3 text-right font-bold text-teal-700">{{ number_format($teacher->student_count, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif

@unless ($isFoundationAdmin)
    <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" data-reveal>
        <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[0.12em] text-violet-700">Direktori terbaru</p><h2 class="mt-1.5 text-xl font-bold text-slate-950">Akun baru ditambahkan</h2></div><a class="text-sm font-bold text-teal-800 underline-offset-4 hover:underline" href="{{ route('admin.users') }}">Buka manajemen akun &rarr;</a></div>
        @if ($latestUsers->isEmpty())
            <p class="mt-5 rounded-xl bg-slate-50 p-5 text-sm text-slate-600">Belum ada akun di portal.</p>
        @else
            <div class="mt-5 overflow-x-auto"><table class="w-full min-w-[680px] text-left text-sm"><thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Nama</th><th class="pb-3 font-semibold">Peran</th><th class="pb-3 font-semibold">Sekolah</th><th class="pb-3 font-semibold">Identitas masuk</th><th class="pb-3 font-semibold">Dibuat</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($latestUsers as $user)<tr class="transition hover:bg-slate-50"><td class="py-3 font-semibold text-slate-900">{{ $user->name }}</td><td class="py-3 text-slate-600">{{ ['guru' => 'Guru', 'siswa' => 'Siswa', 'admin_remote' => 'All Remote', 'admin_yayasan' => 'Yayasan'][$user->role] ?? $user->role }}</td><td class="py-3 text-slate-600">{{ $user->school->name ?? '—' }}</td><td class="py-3 text-slate-600">{{ $user->email ?? $user->nisn }}</td><td class="py-3 text-slate-500">{{ $user->created_at?->format('d M Y') }}</td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
@endunless
@endsection
