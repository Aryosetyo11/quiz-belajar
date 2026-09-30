<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Pilih portal masuk RuangUji.">
    <title>Masuk Portal | RuangUji</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    <main class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-6 py-12">
        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-3 self-center text-center">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 font-bold text-white">RU</span>
            <span class="text-left"><strong class="block text-lg text-slate-900">RuangUji</strong><small class="text-slate-500">Yayasan NC Learning Center</small></span>
        </a>
        <div class="mx-auto w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-10">
            <div class="text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Portal Pengguna</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Pilih jenis akun Anda</h1>
                <p class="mt-2 text-sm text-slate-600">Gunakan portal yang sesuai dengan akun yang diberikan yayasan.</p>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <a href="{{ route('login.teacher') }}" class="rounded-xl border border-slate-200 p-5 transition hover:border-indigo-400 hover:bg-indigo-50/40 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Guru</span>
                    <h2 class="mt-2 font-bold text-slate-900">Portal Pengajar</h2>
                    <p class="mt-1 text-sm text-slate-600">Masuk menggunakan email dan kata sandi.</p>
                    <span class="mt-5 inline-flex text-sm font-semibold text-indigo-600">Lanjutkan <span aria-hidden="true" class="ml-1">→</span></span>
                </a>
                <a href="{{ route('login.student') }}" class="rounded-xl border border-slate-200 p-5 transition hover:border-indigo-400 hover:bg-indigo-50/40 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Siswa</span>
                    <h2 class="mt-2 font-bold text-slate-900">Portal Siswa</h2>
                    <p class="mt-1 text-sm text-slate-600">Masuk menggunakan NISN dan kata sandi.</p>
                    <span class="mt-5 inline-flex text-sm font-semibold text-indigo-600">Lanjutkan <span aria-hidden="true" class="ml-1">→</span></span>
                </a>
            </div>
            <p class="mt-6 text-center text-xs text-slate-500">Belum memiliki akun? Hubungi administrator yayasan.</p>
        </div>
        <a href="{{ route('home') }}" class="mt-6 self-center text-sm font-medium text-slate-500 hover:text-indigo-600">Kembali ke halaman utama</a>
    </main>
</body>
</html>
