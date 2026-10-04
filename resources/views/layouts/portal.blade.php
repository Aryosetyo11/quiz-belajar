<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal pengguna RuangUji.">
    <title>@yield('title', 'Portal RuangUji')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-6">
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white shadow-sm">RU</span>
                <span><strong class="block text-base leading-5 text-slate-950">RuangUji</strong><small class="block text-xs text-slate-500">{{ auth()->user()->role === \App\Models\User::ROLE_TEACHER ? 'Portal Pengajar' : 'Portal Siswa' }}</small></span>
            </a>
            <div class="flex items-center gap-4">
                <span class="hidden text-sm text-slate-600 sm:inline">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Keluar</button>
                </form>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-5 py-9 sm:px-6 sm:py-12">
        @yield('content')
    </main>
</body>
</html>
