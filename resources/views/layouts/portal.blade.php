<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal pengguna RuangUji.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portal RuangUji')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-slate-900 antialiased">
    @php
        $portalLabel = match (auth()->user()->role) {
            \App\Models\User::ROLE_TEACHER => 'Pengajar',
            \App\Models\User::ROLE_STUDENT => 'Siswa',
            \App\Models\User::ROLE_ADMIN_REMOTE => 'Manajemen All Remote',
            \App\Models\User::ROLE_ADMIN_FOUNDATION => 'Manajemen Yayasan',
        };
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[260px_minmax(0,1fr)]" data-portal-shell>
        <aside data-portal-sidebar class="hidden border-r border-slate-200 bg-white shadow-sm lg:fixed lg:inset-y-0 lg:left-0 lg:z-40 lg:flex lg:h-dvh lg:w-[260px] lg:flex-col">
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="flex items-center gap-3 px-6 py-5 transition-colors hover:bg-slate-50">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-sm font-bold text-white shadow-sm">RU</span>
                <span><strong class="block text-base font-semibold text-slate-950">RuangUji</strong><small class="mt-0.5 block text-xs font-medium text-slate-500">Portal {{ $portalLabel }}</small></span>
            </a>
            <div class="mx-4 h-px bg-slate-100"></div>

            <div class="min-h-0 flex-1 overflow-y-auto px-4 pt-4">
                @include('partials.portal-navigation')
            </div>

            <div class="border-t border-slate-100 p-4">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">{{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}</span>
                    <span class="min-w-0 flex-1"><strong class="block truncate text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</strong><small class="block text-xs text-slate-500">{{ $portalLabel }}</small></span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center gap-2 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2.5 text-left text-sm font-semibold text-rose-700 transition duration-150 hover:border-rose-300 hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600 focus-visible:ring-offset-2">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5" /></svg>
                        Keluar dari portal
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 lg:col-start-2">
            <header data-portal-mobile-header class="border-b border-slate-200 bg-white lg:hidden">
                <div class="flex items-center justify-between px-5 py-3.5">
                    <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 to-indigo-800 text-sm font-bold text-white">RU</span>
                        <span><strong class="block text-base font-semibold text-slate-950">RuangUji</strong><small class="block text-xs text-slate-500">Portal {{ $portalLabel }}</small></span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600 focus-visible:ring-offset-2">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 17l5-5-5-5m5 5H3m9-9h5a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5" /></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </header>
            <details data-portal-mobile-nav class="border-b border-slate-200 bg-white px-5 py-2.5 lg:hidden">
                <summary class="flex cursor-pointer list-none items-center justify-between py-2 text-sm font-semibold text-slate-700">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        Menu portal
                    </span>
                    <span class="text-slate-400 transition-transform" aria-hidden="true">⌄</span>
                </summary>
                <div class="pb-4 pt-2">
                    @include('partials.portal-navigation')
                </div>
            </details>
            <main data-portal-motion class="w-full flex-1 px-5 py-7 sm:px-8 lg:px-8 xl:px-10">
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.portal-alert-modal')
</body>
</html>
