<header class="sticky top-0 z-40 border-b border-zinc-200/70 bg-white/95 backdrop-blur-md">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3.5">

        <!-- Brand -->
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-600/30 transition-transform duration-200 group-hover:scale-105">
                <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
            </div>
            <div>
                <span class="text-[15px] font-bold tracking-tight text-zinc-900 block leading-none">RuangUji</span>
                <span class="text-[11px] text-zinc-400 font-medium leading-none mt-0.5 block">NC Learning Center</span>
            </div>
        </a>

        <!-- Nav -->
        <nav class="hidden md:flex items-center gap-7 text-[13.5px] font-medium text-zinc-500" aria-label="Menu Utama">
            <a href="#keunggulan" class="transition-colors hover:text-zinc-900">Keunggulan</a>
            <a href="#keamanan" class="transition-colors hover:text-zinc-900">Keamanan</a>
            <a href="#peran" class="transition-colors hover:text-zinc-900">Akses Pengguna</a>
            <a href="#profil" class="transition-colors hover:text-zinc-900">Tentang</a>
        </nav>

        <!-- CTA -->
        <div class="hidden sm:flex items-center gap-2.5">
            <button type="button" class="open-portal-btn inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-[13px] font-semibold text-white shadow-sm shadow-indigo-600/20 transition-all hover:bg-indigo-700 hover:shadow-md hover:shadow-indigo-600/25 cursor-pointer">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Masuk Portal
            </button>
        </div>

        <!-- Mobile toggle -->
        <button id="mobile-menu-btn" type="button" class="md:hidden rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900" aria-label="Buka Menu" aria-expanded="false">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
            </svg>
        </button>
    </div>

    <!-- Mobile menu -->
    <div id="mobile-menu" class="hidden border-t border-zinc-100 bg-white px-6 py-5 md:hidden">
        <div class="flex flex-col gap-4 text-[14px] font-medium text-zinc-600">
            <a href="#keunggulan" class="hover:text-zinc-900">Keunggulan</a>
            <a href="#keamanan" class="hover:text-zinc-900">Keamanan</a>
            <a href="#peran" class="hover:text-zinc-900">Akses Pengguna</a>
            <a href="#profil" class="hover:text-zinc-900">Tentang</a>
            <div class="pt-3 border-t border-zinc-100">
                <button type="button" class="open-portal-btn w-full rounded-xl bg-indigo-600 py-3 text-center text-[13px] font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Masuk Portal Ujian
                </button>
            </div>
        </div>
    </div>
</header>
