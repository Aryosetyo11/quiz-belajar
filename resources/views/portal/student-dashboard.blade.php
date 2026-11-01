<div>
    <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Dashboard siswa</p>
    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Halo, {{ auth()->user()->name }} 👋</h1>
            <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Pantau ujian yang perlu dikerjakan dan lanjutkan belajar dari mata pelajaranmu.</p>
        </div>
        <a href="{{ route('student.subjects') }}" class="inline-flex w-fit items-center justify-center gap-2 rounded-xl bg-indigo-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-300">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            Lihat mata pelajaran
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<section class="mt-8 grid gap-4 sm:grid-cols-3" aria-label="Ringkasan belajar">
    <article class="group rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-indigo-700">Perlu dikerjakan</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
            </span>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-slate-950">{{ $totalPendingCount }}</p>
        <p class="mt-1 text-sm text-slate-600">Tersedia atau sedang berlangsung</p>
        @if ($scheduledExamCount > 0)
            <p class="mt-1 text-sm font-medium text-blue-700">{{ $scheduledExamCount }} ujian punya jadwal mendatang</p>
        @endif
    </article>
    <article class="group rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-emerald-700">Ujian selesai</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-slate-950">{{ $totalCompletedCount }}</p>
        <p class="mt-1 text-sm text-slate-600">Jawaban sudah dikirim</p>
    </article>
    <article class="group rounded-2xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-md">
        <div class="flex items-center justify-between">
            <p class="text-sm font-semibold text-amber-700">Rata-rata nilai</p>
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
            </span>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-slate-950">{{ $averageScore ?? '—' }}{{ $averageScore !== null ? '' : '' }}</p>
        <p class="mt-1 text-sm text-slate-600">{{ $averageScore !== null ? 'Rata-rata ujian yang sudah dinilai' : 'Belum ada ujian yang dinilai' }}</p>
    </article>
</section>

{{-- Exams requiring attention --}}
<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="pending-exams-title">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="pending-exams-title" class="text-xl font-semibold text-slate-950">Ujian & jadwal</h2>
            <p class="mt-1 text-sm text-slate-600">Lanjutkan ujian aktif, cek ujian yang tersedia, atau lihat jadwal berikutnya.</p>
        </div>
        <a href="{{ route('student.exams') }}" class="text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Lihat semua ujian</a>
    </div>
    <ul class="mt-4 divide-y divide-slate-100">
        @foreach ($pendingExams as $exam)
            @php($attempt = $exam->attempts->first())
            <li class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase text-indigo-600">{{ $exam->subject->name }}</p>
                    <h3 class="mt-0.5 truncate text-base font-semibold text-slate-900">{{ $exam->title }}</h3>
                    @if ($exam->starts_at)
                        <p class="mt-1 text-sm text-slate-600">{{ $exam->starts_at->isFuture() ? 'Dibuka' : 'Mulai' }} {{ $exam->starts_at->format('d M Y, H:i') }}</p>
                    @endif
                    <p class="mt-1 text-xs text-slate-500">{{ $exam->question_count ?? $exam->questions_count ?? 0 }} soal · {{ $exam->duration_minutes }} menit</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if ($attempt && !$attempt->submitted_at && $attempt->hasExpired($exam))
                        <span class="rounded-full bg-rose-100 px-2.5 py-1 text-sm font-semibold text-rose-800">Waktu habis</span>
                        <a href="{{ route('student.exams') }}" class="rounded-lg border border-rose-200 bg-white px-3 py-2 text-sm font-semibold text-rose-800 transition hover:bg-rose-50">Periksa status</a>
                    @elseif ($attempt && !$attempt->submitted_at)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 text-sm font-semibold text-amber-800">Sedang dikerjakan</span>
                        <a href="{{ route('student.exams.show', $exam) }}" class="rounded-lg bg-amber-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-amber-800">Lanjutkan</a>
                    @elseif ($exam->starts_at?->isFuture())
                        <span class="rounded-full bg-blue-100 px-2.5 py-1 text-sm font-semibold text-blue-800">Terjadwal</span>
                    @else
                        <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-sm font-semibold text-indigo-800">Tersedia</span>
                        <a href="{{ route('student.exams.show', $exam) }}" class="rounded-lg bg-indigo-700 px-3 py-2 text-sm font-semibold text-white transition hover:bg-indigo-800">Lihat instruksi</a>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
    @if ($pendingExams->isEmpty())
        <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-7 text-center">
            <h3 class="text-base font-semibold text-slate-900">Tidak ada ujian yang perlu dikerjakan</h3>
            <p class="mt-1 text-sm leading-6 text-slate-600">Gunakan waktu ini untuk membuka materi di mata pelajaranmu. Jadwal ujian yang akan datang akan tampil di sini.</p>
            <a href="{{ route('student.subjects') }}" class="mt-4 inline-flex rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Buka materi belajar</a>
        </div>
    @endif
</section>

{{-- Subjects --}}
<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="student-subjects-title">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="student-subjects-title" class="text-xl font-semibold text-slate-950">Mata pelajaran saya</h2>
            <p class="mt-1 text-sm text-slate-600">{{ $studentSubjectCount }} mata pelajaran terdaftar</p>
        </div>
        @if ($studentSubjects->isNotEmpty())
            <a href="{{ route('student.subjects') }}" class="text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Lihat semua</a>
        @endif
    </div>

    @if ($studentSubjects->isEmpty())
        <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
            <svg class="mx-auto h-10 w-10 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            <h3 class="mt-3 text-base font-semibold text-slate-900">Belum ada mata pelajaran</h3>
            <p class="mx-auto mt-2 max-w-lg text-sm leading-7 text-slate-600">Mata pelajaran akan muncul setelah pengajar mendaftarkan akun Anda. Pastikan NISN pada profil sudah benar.</p>
            <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-100">Periksa profil</a>
        </div>
    @else
        <ul class="mt-5 space-y-3">
            @foreach ($studentSubjects->take(4) as $subject)
                <li class="group rounded-xl border border-slate-100 bg-slate-50 p-4 transition hover:border-indigo-200 hover:bg-indigo-50/40">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-semibold text-slate-900">{{ $subject->name }}</h3>
                            <p class="mt-1 text-sm text-slate-600">Pengajar: {{ $subject->teacher->name }}</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-sm font-medium">
                                <span class="rounded-md bg-indigo-50 px-2 py-1 text-indigo-800">{{ $subject->documents_count }} modul</span>
                                <span class="rounded-md bg-rose-50 px-2 py-1 text-rose-800">{{ $subject->videos_count }} video</span>
                                @if ($subject->links_count > 0)
                                    <span class="rounded-md bg-emerald-50 px-2 py-1 text-emerald-800">{{ $subject->links_count }} tautan</span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('student.subjects.show', $subject) }}" class="shrink-0 rounded-lg border border-indigo-200 bg-white px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-700 hover:text-white">Buka materi</a>
                    </div>
                </li>
            @endforeach
        </ul>
        @if ($studentSubjects->count() > 4)
            <p class="mt-4 text-sm text-slate-600">Menampilkan 4 dari {{ $studentSubjectCount }} mata pelajaran.</p>
        @endif
    @endif
</section>

@if ($completedExams->isNotEmpty())
    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="recent-results-title">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="recent-results-title" class="text-xl font-semibold text-slate-950">Ujian terakhir</h2>
                <p class="mt-1 text-sm text-slate-600">Lihat nilai terbaru atau cek esai yang masih menunggu penilaian.</p>
            </div>
            <a href="{{ route('student.exams') }}" class="text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Riwayat ujian</a>
        </div>
        <ul class="mt-4 divide-y divide-slate-100">
            @foreach ($completedExams as $exam)
                @php($attempt = $exam->attempts->first())
                <li class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-600">{{ $exam->subject->name }}</p>
                        <h3 class="mt-0.5 truncate text-base font-semibold text-slate-900">{{ $exam->title }}</h3>
                        <p class="mt-1 text-sm text-slate-500">Dikirim {{ $attempt->submitted_at->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @if ($attempt->score !== null)
                            <span class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800">Nilai {{ $attempt->score }}/100</span>
                        @else
                            <span class="rounded-lg bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">Menunggu nilai esai</span>
                        @endif
                        <a href="{{ route('student.exams.show', $exam) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Lihat</a>
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
@endif
