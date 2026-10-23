<section id="keunggulan" class="bg-gradient-to-b from-white via-indigo-50/30 to-white py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-6">
        <div class="mx-auto mb-10 max-w-2xl text-center" data-reveal><span class="label-eyebrow">Fokus RuangUji</span><h2 class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-950 sm:text-4xl">Dibangun untuk kuis dan ujian</h2><p class="mt-3 text-base leading-7 text-zinc-600">Kelas dan materi menyiapkan alur belajar; evaluasi digital menjadi arah utama pengembangan RuangUji.</p></div>
        <div class="grid gap-4 md:grid-cols-3">
            @foreach ([['Kelola kelas', 'Guru membuat mata pelajaran dan mendaftarkan siswa sebagai peserta.', 'bg-indigo-50 text-indigo-700', '01', 'Tersedia'], ['Bagikan materi', 'Materi kelas membantu siswa menyiapkan diri sebelum evaluasi.', 'bg-teal-50 text-teal-700', '02', 'Tersedia'], ['Kuis dan ujian digital', 'Fokus utama RuangUji: jadwal evaluasi, pengerjaan kuis, dan hasil belajar.', 'bg-amber-50 text-amber-800', '03', 'Tahap berikutnya']] as [$title, $description, $color, $number, $status])
                <article class="group rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg" data-reveal><div class="flex items-center justify-between"><span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $color }} text-sm font-extrabold">{{ $number }}</span><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">{{ $status }}</span></div><h3 class="mt-5 text-lg font-bold text-zinc-950">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-zinc-600">{{ $description }}</p></article>
            @endforeach
        </div>
    </div>
</section>
