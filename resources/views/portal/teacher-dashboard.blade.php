<div>
    <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Dashboard pengajar</p>
    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Ringkasan mata pelajaran dan siswa yang sedang Anda kelola.</p>
        </div>
        <a href="{{ route('teacher.subjects') }}" class="inline-flex w-fit items-center justify-center rounded-lg bg-slate-900 px-4 py-3 text-base font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Kelola mata pelajaran</a>
    </div>
</div>

<section class="mt-8 grid gap-4 sm:grid-cols-2" aria-label="Ringkasan pengajar">
    <article class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
        <p class="text-base font-medium text-slate-600">Mata pelajaran</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $subjectCount }}</p>
        <p class="mt-1 text-sm text-slate-500">Dikelola oleh akun Anda</p>
    </article>
    <article class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
        <p class="text-base font-medium text-slate-600">Pendaftaran siswa</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">{{ $studentEnrollmentCount }}</p>
        <p class="mt-1 text-sm text-slate-500">Jumlah keikutsertaan di semua mata pelajaran</p>
    </article>
</section>

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="subject-enrollment-title">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="subject-enrollment-title" class="text-xl font-semibold text-slate-950">Siswa per mata pelajaran</h2>
            <p class="mt-1 text-base text-slate-600">Distribusi pendaftaran pada mata pelajaran yang Anda kelola.</p>
        </div>
        @if ($teacherSubjects->isNotEmpty())
            <a href="{{ route('teacher.subjects') }}" class="text-base font-semibold text-indigo-700 underline-offset-4 hover:underline">Lihat semua</a>
        @endif
    </div>

    @if ($teacherSubjects->isEmpty())
        <div class="mt-5 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
            <h3 class="text-lg font-semibold text-slate-900">Belum ada mata pelajaran</h3>
            <p class="mt-2 text-base text-slate-600">Buat mata pelajaran untuk mulai mendaftarkan siswa.</p>
            <a href="{{ route('teacher.subjects') }}" class="mt-4 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base font-semibold text-slate-800 transition hover:bg-slate-100">Buat mata pelajaran</a>
        </div>
    @else
        <ul class="mt-5 space-y-5">
            @foreach ($teacherSubjects->take(5) as $subject)
                @php($barWidth = (int) round(($subject->students_count / $largestSubjectEnrollment) * 100))
                <li>
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <span class="min-w-0 truncate text-base font-medium text-slate-800">{{ $subject->name }}</span>
                        <span class="shrink-0 text-sm text-slate-600">{{ $subject->students_count }} siswa</span>
                    </div>
                    <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Jumlah siswa pada {{ $subject->name }}" aria-valuemin="0" aria-valuemax="{{ $largestSubjectEnrollment }}" aria-valuenow="{{ $subject->students_count }}">
                        <div class="h-full rounded-full bg-indigo-600 transition-[width] duration-500" style="width: {{ $barWidth }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
        @if ($teacherSubjects->count() > 5)
            <p class="mt-4 text-sm text-slate-500">Menampilkan 5 dari {{ $subjectCount }} mata pelajaran.</p>
        @endif
    @endif
</section>

<section class="mt-6 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6" aria-labelledby="exam-module-title">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.08em] text-amber-700">Tahap berikutnya</p>
        <h2 id="exam-module-title" class="mt-1 text-lg font-semibold text-slate-950">Modul ujian belum tersedia</h2>
        <p class="mt-1 max-w-2xl text-base leading-7 text-slate-600">Pembuatan ujian, pemantauan, dan hasil penilaian akan ditambahkan setelah modulnya siap.</p>
    </div>
    <span class="w-fit shrink-0 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-800">Belum aktif</span>
</section>
