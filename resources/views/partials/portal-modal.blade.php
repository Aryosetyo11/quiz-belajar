<div id="portal-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm">
    <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl sm:p-7">

        <!-- Close Button -->
        <button type="button" class="close-portal-btn absolute top-4 right-4 rounded-lg p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 cursor-pointer" aria-label="Tutup">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>

        <!-- Modal Header -->
        <div class="mb-6 pr-8">
            <h3 class="text-lg font-semibold text-slate-950">Masuk ke RuangUji</h3>
            <p class="mt-1 text-sm leading-6 text-slate-600">Pilih portal sesuai akun yang Anda miliki.</p>
        </div>

        <!-- Role Options -->
        <div class="space-y-3">

            <a href="{{ route('login.teacher') }}" class="group block rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/40 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700">Pengajar</h4>
                        <p class="mt-1 text-sm text-slate-600">Masuk menggunakan email.</p>
                    </div>
                    <span class="text-sm font-medium text-indigo-700" aria-hidden="true">&rarr;</span>
                </div>
            </a>

            <a href="{{ route('login.student') }}" class="group block rounded-xl border border-slate-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/40 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 group-hover:text-indigo-700">Siswa</h4>
                        <p class="mt-1 text-sm text-slate-600">Masuk menggunakan NISN.</p>
                    </div>
                    <span class="text-sm font-medium text-indigo-700" aria-hidden="true">&rarr;</span>
                </div>
            </a>

        </div>

        <!-- Notice -->
        <div class="mt-5 rounded-lg bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">
            Guru masuk menggunakan email; siswa menggunakan NISN.
        </div>

        <div class="mt-5 flex justify-end">
            <button type="button" class="close-portal-btn rounded-lg px-4 py-2 text-[13px] font-semibold text-zinc-500 hover:bg-zinc-100 hover:text-zinc-800 cursor-pointer transition-colors">
                Tutup
            </button>
        </div>

    </div>
</div>
