<div class="flex flex-col gap-2 border-b border-slate-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-indigo-700">Portal Pengajar</p>
        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-950 sm:text-3xl">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600">Pilih area kerja untuk melanjutkan persiapan evaluasi.</p>
    </div>
    <span class="inline-flex w-fit rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-medium text-amber-800">Fitur inti sedang disiapkan</span>
</div>

<div class="mt-7 grid gap-4 md:grid-cols-3">
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Bank soal</p>
        <h2 class="mt-3 text-lg font-semibold text-slate-950">Siapkan evaluasi</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Tambahkan soal, susun materi, dan buat kuis baru.</p>
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Sesi ujian</p>
        <h2 class="mt-3 text-lg font-semibold text-slate-950">Atur pelaksanaan</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Tentukan jadwal, durasi, dan peserta untuk setiap sesi.</p>
    </section>
    <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Hasil belajar</p>
        <h2 class="mt-3 text-lg font-semibold text-slate-950">Tinjau hasil</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Lihat nilai dan catatan pengerjaan saat data evaluasi tersedia.</p>
    </section>
</div>
