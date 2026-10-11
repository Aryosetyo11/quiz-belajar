<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal pengguna RuangUji.">
    <title>@yield('title', 'Portal RuangUji')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-slate-900 antialiased">
    @php
        $isTeacher = auth()->user()->role === \App\Models\User::ROLE_TEACHER;
        $portalLabel = $isTeacher ? 'Pengajar' : 'Siswa';
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[248px_minmax(0,1fr)]">
        <aside class="hidden border-r border-slate-200 bg-white lg:flex lg:flex-col">
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="flex items-center gap-3 px-6 py-6">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-700 text-sm font-bold text-white">RU</span>
                <span><strong class="block text-base font-semibold text-slate-950">RuangUji</strong><small class="mt-0.5 block text-sm text-slate-500">Portal {{ $portalLabel }}</small></span>
            </a>

            <div class="px-4 pt-5">
                @include('partials.portal-navigation')
            </div>

            <div class="mt-auto border-t border-slate-100 p-4">
                <div class="flex items-center gap-3 px-2 py-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-700">{{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                    <span class="min-w-0"><strong class="block truncate text-base font-medium text-slate-800">{{ auth()->user()->name }}</strong><small class="block text-sm text-slate-500">{{ $portalLabel }}</small></span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-3 text-left text-base font-semibold text-rose-800 transition hover:border-rose-300 hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600 focus-visible:ring-offset-2">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5" /></svg>
                        Keluar dari portal
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0">
            <header class="border-b border-slate-200 bg-white lg:hidden">
                <div class="flex items-center justify-between px-5 py-3.5">
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-700 text-sm font-bold text-white">RU</span>
                        <span><strong class="block text-base font-semibold text-slate-950">RuangUji</strong><small class="block text-sm text-slate-500">Portal {{ $portalLabel }}</small></span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-base font-semibold text-rose-800 transition hover:border-rose-300 hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600 focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5" /></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </header>
            <details class="border-b border-slate-200 bg-white px-5 py-2.5 lg:hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between py-2 text-base font-medium text-slate-700">
                    Menu portal
                    <span class="text-base text-slate-500" aria-hidden="true">⌄</span>
                </summary>
                <div class="py-4">
                    @include('partials.portal-navigation')
                </div>
            </details>
            <main class="mx-auto w-full max-w-6xl px-5 py-8 sm:px-8 sm:py-10 xl:px-12">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
