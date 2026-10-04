<div class="border-b border-slate-200 pb-7">
    <p class="text-sm font-medium text-indigo-700">Portal Siswa</p>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Halo, {{ auth()->user()->name }}</h1>
    <p class="mt-2 text-sm leading-6 text-slate-600">Ujian yang dibuka pengajar akan muncul di halaman ini.</p>
</div>

<section class="mt-7 rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M6 5h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
        </svg>
    </div>
    <h2 class="mt-5 text-lg font-semibold text-slate-950">Belum ada ujian tersedia</h2>
    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-600">Saat pengajar membuka sesi ujian untuk Anda, judul, jadwal, dan tombol mulai akan tampil di sini.</p>
</section>
