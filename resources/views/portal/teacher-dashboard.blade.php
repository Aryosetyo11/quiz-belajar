<div class="space-y-7">
    {{-- Header Section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Dashboard pengajar <span class="font-medium normal-case tracking-normal text-slate-500">· {{ now()->translatedFormat('l, d F Y') }}</span></p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">
                Halo, {{ auth()->user()->name }} 👋
            </h1>
            <p class="mt-2 text-base leading-7 text-slate-600">
                Ringkasan kegiatan pembelajaran, kuis evaluasi, dan capaian nilai siswa Anda hari ini.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('teacher.subjects') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-2xs transition hover:bg-slate-50 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                Semua Kelas
            </a>
            <a href="{{ route('teacher.exam-sessions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                + Buat Ujian Baru
            </a>
        </div>
    </div>

    {{-- Smart Alert: Perlu Perhatian Guru (Hanya Tampil Jika Ada Hal Penting) --}}
    @if ($violationAlertCount > 0 || $remedialCount > 0)
        <div class="rounded-2xl border border-amber-200/90 bg-amber-50/60 p-4 sm:p-5">
            <div class="flex items-start gap-3.5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </span>
                <div class="flex-1">
                    <h2 class="text-sm font-bold text-amber-900">Perlu Perhatian Anda Hari Ini:</h2>
                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-amber-800">
                        @if ($violationAlertCount > 0)
                            <span class="inline-flex items-center gap-1 font-semibold text-rose-700">
                                • {{ $violationAlertCount }} pengerjaan terhenti sistem proctoring
                            </span>
                        @endif
                        @if ($remedialCount > 0)
                            <span class="inline-flex items-center gap-1 text-amber-800">
                                • {{ $remedialCount }} siswa meraih nilai di bawah KKM (&lt; 70)
                            </span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('teacher.results') }}" class="shrink-0 rounded-lg border border-amber-300 bg-white px-3 py-1.5 text-sm font-bold text-amber-900 shadow-2xs hover:bg-amber-50 transition">
                    Tinjau Hasil &rarr;
                </a>
            </div>
        </div>
    @endif

    {{-- 4 Kartu Metrik Informatif --}}
    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Statistik Pembelajaran">
        {{-- Card 1: Kelas Diampu --}}
        <a href="{{ route('teacher.subjects') }}" class="group relative rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs transition hover:border-indigo-300 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold uppercase tracking-wider text-slate-500">Mata Pelajaran</span>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition group-hover:bg-indigo-600 group-hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $subjectCount }}</span>
                <span class="text-sm font-semibold text-slate-500">ruang kelas</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $totalMaterials }} total materi modul/video</p>
        </a>

        {{-- Card 2: Total Siswa --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold uppercase tracking-wider text-slate-500">Total Partisipan</span>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $studentEnrollmentCount }}</span>
                <span class="text-sm font-semibold text-slate-500">siswa</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Terdaftar di seluruh kelas Anda</p>
        </div>

        {{-- Card 3: Ujian Berjalan --}}
        <a href="{{ route('teacher.exam-sessions') }}" class="group relative rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs transition hover:border-emerald-300 hover:shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold uppercase tracking-wider text-slate-500">Ujian Berjalan</span>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">{{ $activeExams->count() }}</span>
                <span class="text-sm font-semibold text-slate-500">sesi kuis</span>
            </div>
            <p class="mt-1 text-sm text-slate-500">Sedang dapat diakses siswa</p>
        </a>

        {{-- Card 4: Rata-rata Nilai & Kelulusan (Informatif) --}}
        <div class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-sm font-bold uppercase tracking-wider text-slate-500">Rata-rata Nilai</span>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                </span>
            </div>
            <div class="mt-3 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-900">
                    {{ $averageScore !== null ? number_format($averageScore, 1) : '—' }}
                </span>
                @if ($passRate !== null)
                    <span class="inline-flex items-center text-sm font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                        {{ $passRate }}% Tuntas KKM
                    </span>
                @else
                    <span class="text-sm text-slate-400">Belum ada nilai</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">Dari {{ $totalSubmissions }} kali pengerjaan siswa</p>
        </div>
    </section>

    {{-- Section: Ruang Kelas Saya (Lengkap dengan Status Ujian Aktif) --}}
    <section class="space-y-4" aria-labelledby="classes-heading">
        <div class="flex items-center justify-between">
            <div>
                <h2 id="classes-heading" class="text-lg font-bold text-slate-900">Kelas yang Anda Ajar</h2>
                <p class="text-sm text-slate-500">Pilih ruang kelas untuk membagikan modul baru atau melihat daftar siswa</p>
            </div>
            @if ($teacherSubjects->isNotEmpty())
                <a href="{{ route('teacher.subjects') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                    Kelola Semua Kelas &rarr;
                </a>
            @endif
        </div>

        @if ($teacherSubjects->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                </div>
                <h3 class="mt-3 text-base font-bold text-slate-900">Belum Ada Kelas Mata Pelajaran</h3>
                <p class="mt-1 text-sm text-slate-500">Mulai dengan membuat kelas pertama Anda untuk mendaftarkan siswa dan mengunggah materi kuis.</p>
                <a href="{{ route('teacher.subjects') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    + Buat Kelas Baru Sekarang
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($teacherSubjects->take(3) as $subject)
                    <article class="flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs transition hover:border-indigo-300 hover:shadow-sm">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold text-white shadow-2xs">
                                        {{ mb_substr($subject->name, 0, 1) }}
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-bold text-slate-900">
                                            {{ $subject->name }}
                                        </h3>
                                        <p class="text-sm text-slate-500">
                                            {{ $subject->students_count }} siswa terdaftar
                                        </p>
                                    </div>
                                </div>

                                {{-- Status Ujian di Kelas Ini --}}
                                @if (($subject->active_exams_count ?? 0) > 0)
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-sm font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $subject->active_exams_count }} Ujian Aktif
                                    </span>
                                @endif
                            </div>

                            <p class="mt-3 text-sm text-slate-600 line-clamp-2 leading-relaxed">
                                {{ $subject->description ?: 'Belum ada deskripsi untuk ruang kelas ini.' }}
                            </p>
                        </div>

                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-3.5">
                            <span class="text-sm font-medium text-slate-500">
                                {{ $subject->materials_count ?? 0 }} modul • {{ $subject->exams_count ?? 0 }} kuis
                            </span>
                            <a href="{{ route('teacher.subjects.show', $subject) }}" class="inline-flex items-center gap-1 text-sm font-bold text-indigo-600 hover:text-indigo-800 transition">
                                Masuk Kelas &rarr;
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Dua Kolom Informasi Esensial: Ujian Berjalan & Siswa yang Baru Mengumpulkan --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Kolom 1: Ujian yang Sedang Berjalan --}}
        <section class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-2xs" aria-labelledby="active-exams-heading">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 id="active-exams-heading" class="text-base font-bold text-slate-900">Ujian yang Sedang Berjalan</h2>
                    <p class="text-sm text-slate-500">Kuis yang dapat diakses siswa saat ini</p>
                </div>
                <a href="{{ route('teacher.exam-sessions') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                    Kelola Sesi Ujian &rarr;
                </a>
            </div>

            @if ($activeExams->isNotEmpty())
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($activeExams as $exam)
                        <div class="flex items-center justify-between gap-4 py-3.5 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-sm font-bold text-slate-900">{{ $exam->title }}</h3>
                                <p class="mt-0.5 text-sm text-slate-500">
                                    <span class="font-semibold text-indigo-700">{{ $exam->subject->name ?? 'Mata Pelajaran' }}</span>
                                    • {{ $exam->duration_minutes }} menit
                                    • {{ $exam->question_count ?? $exam->questions_count ?? 0 }} butir soal
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="block text-sm font-bold text-slate-900">{{ $exam->attempts_count }}</span>
                                <span class="block text-xs text-slate-400">pengerjaan</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-sm text-slate-500">
                    <p>Tidak ada ujian yang sedang aktif.</p>
                    <a href="{{ route('teacher.exam-sessions.create') }}" class="mt-2 inline-block font-semibold text-indigo-600 hover:underline">
                        + Buat sesi ujian baru
                    </a>
                </div>
            @endif
        </section>

        {{-- Kolom 2: Pengumpulan Ujian Terkini --}}
        <section class="rounded-2xl border border-slate-200/90 bg-white p-5 sm:p-6 shadow-2xs" aria-labelledby="recent-submissions-heading">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <h2 id="recent-submissions-heading" class="text-base font-bold text-slate-900">Pengumpulan Ujian Terkini</h2>
                    <p class="text-sm text-slate-500">Hasil pengerjaan siswa yang baru masuk</p>
                </div>
                <a href="{{ route('teacher.results') }}" class="text-sm font-semibold text-indigo-600 hover:underline">
                    Rekap Semua Nilai &rarr;
                </a>
            </div>

            @if ($recentAttempts->isNotEmpty())
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach ($recentAttempts as $attempt)
                        <div class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-bold text-slate-900">
                                        {{ $attempt->student->name ?? 'Siswa' }}
                                    </p>
                                    @if ($attempt->is_terminated_by_system)
                                        <span class="rounded bg-rose-100 px-1.5 py-0.2 text-xs font-bold text-rose-700">Terhenti</span>
                                    @endif
                                </div>
                                <p class="truncate text-sm text-slate-500">
                                    {{ $attempt->exam->title ?? 'Ujian' }} • <span class="text-slate-400">{{ $attempt->submitted_at?->diffForHumans() }}</span>
                                </p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span @class([
                                    'inline-flex items-center justify-center rounded-lg px-2.5 py-1 text-sm font-bold',
                                    'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' => ($attempt->score ?? 0) >= 70,
                                    'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/20' => ($attempt->score ?? 0) < 70,
                                ])>
                                    Nilai: {{ $attempt->score !== null ? number_format($attempt->score, 0) : '—' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center text-sm text-slate-500">
                    <p>Belum ada siswa yang mengumpulkan jawaban kuis.</p>
                </div>
            @endif
        </section>
    </div>
</div>
