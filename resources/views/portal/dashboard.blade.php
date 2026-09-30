<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal pengguna RuangUji.">
    <title>Portal {{ $role === \App\Models\User::ROLE_TEACHER ? 'Pengajar' : 'Siswa' }} | RuangUji</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    @php($isTeacher = $role === \App\Models\User::ROLE_TEACHER)
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route($isTeacher ? 'teacher.dashboard' : 'student.dashboard') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 font-bold text-white">RU</span>
                <span><strong class="block text-base text-slate-900">RuangUji</strong><small class="text-xs text-slate-500">Portal {{ $isTeacher ? 'Pengajar' : 'Siswa' }}</small></span>
            </a>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm font-medium text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Keluar</button>
                </form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-6 py-10 sm:py-14">
        <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ $isTeacher ? 'Ruang Pengajar' : 'Ruang Siswa' }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Halo, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-slate-600">{{ $isTeacher ? 'Kelola persiapan evaluasi dan pantau aktivitas pembelajaran.' : 'Lihat informasi dan aktivitas evaluasi belajar Anda.' }}</p>

        @if ($isTeacher)
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengelolaan evaluasi</span>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">Bank soal</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Ruang untuk menyiapkan dan mengelola materi soal akan tersedia di sini.</p>
                    <span class="mt-4 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Segera tersedia</span>
                </section>
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sesi ujian</span>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">Jadwal dan pemantauan</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Pengaturan sesi dan pemantauan peserta akan dikelola melalui menu ini.</p>
                    <span class="mt-4 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Segera tersedia</span>
                </section>
                <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hasil belajar</span>
                    <h2 class="mt-2 text-lg font-bold text-slate-900">Rekap nilai</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">Rekapitulasi nilai dan evaluasi jawaban akan muncul di sini.</p>
                    <span class="mt-4 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">Segera tersedia</span>
                </section>
            </div>
        @else
            <section class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Daftar evaluasi</span>
                <h2 class="mt-2 text-xl font-bold text-slate-900">Belum ada ujian tersedia</h2>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Jika pengajar membuka sesi ujian untuk Anda, informasinya akan ditampilkan di halaman ini.</p>
            </section>
        @endif
    </main>
</body>
</html>
