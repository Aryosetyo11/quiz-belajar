<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Masuk ke portal RuangUji.">
    <title>Masuk {{ $role === \App\Models\User::ROLE_TEACHER ? 'Guru' : 'Siswa' }} | RuangUji</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    @php
        $isTeacher = $role === \App\Models\User::ROLE_TEACHER;
        $identifierField = $isTeacher ? 'email' : 'nisn';
        $identifierLabel = $isTeacher ? 'Email' : 'NISN';
        $loginRoute = $isTeacher ? 'login.teacher.store' : 'login.student.store';
    @endphp
    <main class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-6 py-12">
        <a href="{{ route('home') }}" class="mb-8 inline-flex items-center gap-3 self-center">
            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-600 font-bold text-white">RU</span>
            <span><strong class="block text-lg text-slate-900">RuangUji</strong><small class="text-slate-500">Yayasan NC Learning Center</small></span>
        </a>
        <section class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-9">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">← Pilih portal</a>
            <p class="mt-6 text-xs font-bold uppercase tracking-wider text-indigo-600">Portal {{ $isTeacher ? 'Pengajar' : 'Siswa' }}</p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Masuk ke akun Anda</h1>
            <p class="mt-2 text-sm text-slate-600">Masukkan {{ strtolower($identifierLabel) }} dan kata sandi yang terdaftar.</p>

            <form class="mt-7 space-y-5" method="POST" action="{{ route($loginRoute) }}">
                @csrf
                <div>
                    <label for="{{ $identifierField }}" class="mb-1.5 block text-sm font-semibold text-slate-700">{{ $identifierLabel }}</label>
                    <input id="{{ $identifierField }}" name="{{ $identifierField }}" type="{{ $isTeacher ? 'email' : 'text' }}" @if ($isTeacher) autocomplete="username" @else inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="username" @endif value="{{ old($identifierField) }}" required autofocus class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 @error($identifierField) border-red-400 @enderror" aria-describedby="{{ $identifierField }}-error">
                    @error($identifierField)
                        <p id="{{ $identifierField }}-error" class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700">Kata sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 @error('password') border-red-400 @enderror" aria-describedby="password-error">
                    @error('password')
                        <p id="password-error" class="mt-1.5 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Masuk ke Portal</button>
            </form>
            <p class="mt-6 text-center text-xs text-slate-500">Kendala akses? Hubungi administrator yayasan.</p>
        </section>
        <a href="{{ route('home') }}" class="mt-6 self-center text-sm font-medium text-slate-500 hover:text-indigo-600">Kembali ke halaman utama</a>
    </main>
</body>
</html>
