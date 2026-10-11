<div>
    <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Dashboard siswa</p>
    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Lihat mata pelajaran yang terhubung ke akun belajar Anda.</p>
        </div>
        <a href="{{ route('student.subjects') }}" class="inline-flex w-fit items-center justify-center rounded-lg bg-slate-900 px-4 py-3 text-base font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Lihat mata pelajaran</a>
    </div>
</div>

<section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="student-subjects-title">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 id="student-subjects-title" class="text-xl font-semibold text-slate-950">Mata pelajaran saya</h2>
            <p class="mt-1 text-base text-slate-600">{{ $studentSubjectCount }} mata pelajaran terdaftar</p>
        </div>
        @if ($studentSubjects->isNotEmpty())
            <a href="{{ route('student.subjects') }}" class="text-base font-semibold text-indigo-700 underline-offset-4 hover:underline">Lihat semua</a>
        @endif
    </div>

    @if ($studentSubjects->isEmpty())
        <div class="mt-5 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
            <h3 class="text-lg font-semibold text-slate-900">Belum ada mata pelajaran</h3>
            <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Mata pelajaran akan muncul setelah pengajar mendaftarkan akun Anda. Pastikan NISN pada profil sudah benar.</p>
            <a href="{{ route('profile.edit') }}" class="mt-4 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-base font-semibold text-slate-800 transition hover:bg-slate-100">Periksa profil</a>
        </div>
    @else
        <ul class="mt-5 divide-y divide-slate-100">
            @foreach ($studentSubjects->take(4) as $subject)
                <li class="flex flex-col gap-1 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-semibold text-slate-900">{{ $subject->name }}</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">{{ $subject->materials_count }} materi · Pengajar: {{ $subject->teacher->name }}</p>
                    </div>
                    <a href="{{ route('student.subjects.show', $subject) }}" class="shrink-0 text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Buka materi</a>
                </li>
            @endforeach
        </ul>
        @if ($studentSubjects->count() > 4)
            <p class="mt-4 text-sm text-slate-500">Menampilkan 4 dari {{ $studentSubjectCount }} mata pelajaran.</p>
        @endif
    @endif
</section>

<section class="mt-6 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6" aria-labelledby="student-exam-title">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.08em] text-amber-700">Informasi ujian</p>
        <h2 id="student-exam-title" class="mt-1 text-lg font-semibold text-slate-950">Fitur ujian belum tersedia</h2>
        <p class="mt-1 max-w-2xl text-base leading-7 text-slate-600">Jadwal dan petunjuk ujian akan muncul di sini setelah fitur ujian dibuat.</p>
    </div>
    <a href="{{ route('student.exams') }}" class="inline-flex w-fit shrink-0 rounded-lg border border-slate-300 px-4 py-2.5 text-base font-semibold text-slate-700 transition hover:bg-slate-50">Lihat halaman ujian</a>
</section>
