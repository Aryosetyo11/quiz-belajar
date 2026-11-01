<section class="relative isolate overflow-hidden border-b border-indigo-100 bg-gradient-to-br from-indigo-50 via-white to-amber-50/70 py-14 sm:py-20 lg:py-24">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_80%_20%,rgba(129,140,248,.15),transparent_32%),radial-gradient(circle_at_10%_90%,rgba(251,191,36,.12),transparent_28%)]"></div>
    <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-6 lg:grid-cols-[1.05fr_.95fr] lg:gap-16">
        <div class="space-y-6">
            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-white/90 px-4 py-2 text-xs font-bold text-indigo-800 shadow-sm"><span class="h-2 w-2 rounded-full bg-indigo-600"></span>NC Learning Center · Portal evaluasi digital</div>
            <h1 class="max-w-2xl text-4xl font-extrabold leading-[1.08] tracking-tight text-zinc-950 sm:text-5xl lg:text-[3.75rem]">Ujian lebih tertata.<br><span class="bg-gradient-to-r from-indigo-700 via-violet-600 to-teal-700 bg-clip-text text-transparent">Belajar lebih terarah.</span></h1>
            <p class="max-w-xl text-base leading-7 text-zinc-600 sm:text-lg">RuangUji membantu siswa belajar dari materi kelas dan mengikuti ujian digital. Guru mengelola kelas, bank soal, sesi ujian, pemantauan, dan penilaian dalam satu portal.</p>
            <div class="flex flex-wrap gap-2 text-xs font-semibold sm:text-sm"><span class="rounded-full border border-teal-200 bg-teal-50 px-3 py-2 text-teal-900">Materi dan kelas</span><span class="rounded-full border border-indigo-200 bg-indigo-50 px-3 py-2 text-indigo-900">Ujian dan hasil belajar</span></div>
            <div class="flex flex-wrap items-center gap-3 pt-1"><a href="{{ route('login.student') }}" class="inline-flex items-center gap-2.5 rounded-xl bg-indigo-700 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-700/20 transition hover:-translate-y-0.5 hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">Masuk sebagai siswa <span aria-hidden="true">&rarr;</span></a><a href="#peran" class="inline-flex items-center rounded-xl border border-zinc-300 bg-white/90 px-6 py-3.5 text-sm font-bold text-zinc-700 transition hover:border-indigo-300 hover:bg-indigo-50">Kenali RuangUji</a></div>
            <p class="text-sm text-zinc-500">Akun diberikan oleh pengelola sekolah atau yayasan.</p>
        </div>

        <div class="relative" data-reveal>
            <div class="absolute -inset-5 rounded-[2rem] bg-gradient-to-br from-indigo-200/60 via-amber-100/70 to-teal-100/70 blur-2xl"></div>
            <article class="relative rounded-3xl border border-white bg-white p-5 shadow-2xl shadow-indigo-950/10 ring-1 ring-zinc-900/5 sm:p-6" aria-label="Simulasi interaktif kuis">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-4"><div class="flex items-center gap-3"><span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700">Q</span><div><p class="text-xs font-bold uppercase tracking-wider text-indigo-700">Coba langsung</p><h2 class="mt-0.5 text-base font-bold text-zinc-900">Kuis Matematika</h2></div></div><span class="rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-800">Simulasi</span></div>
                <div class="mt-5 flex items-center justify-between text-xs font-semibold text-zinc-500"><span>Persamaan dasar</span><span>Pilihan ganda</span></div>
                <div class="mt-5 rounded-2xl bg-gradient-to-br from-indigo-50 to-sky-50 p-4 sm:p-5"><p class="text-xs font-bold uppercase tracking-wide text-indigo-700">Pertanyaan</p><p class="mt-2 text-base font-bold leading-7 text-zinc-900">Jika 3x + 2 = 11, berapa nilai x?</p></div>
                <div class="mt-5 grid gap-2 sm:grid-cols-2" data-demo-answer-group role="group" aria-label="Pilihan jawaban simulasi kuis">
                    @foreach ([['A', '3'], ['B', '4'], ['C', '2'], ['D', '5']] as [$letter, $answer])
                        <button type="button" data-demo-answer="{{ $letter }}" aria-pressed="false" class="demo-answer flex items-center gap-3 rounded-xl border border-zinc-200 p-3.5 text-left text-sm font-semibold text-zinc-700"><span class="answer-indicator flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-zinc-300 text-xs font-bold">{{ $letter }}</span>{{ $answer }}</button>
                    @endforeach
                </div>
                <div class="mt-5 flex flex-col gap-3 border-t border-zinc-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p data-demo-preview-result class="text-sm leading-6 text-zinc-600" aria-live="polite">Pilih jawaban, lalu periksa hasilnya.</p>
                    <button type="button" data-demo-action disabled class="shrink-0 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800 disabled:cursor-not-allowed disabled:bg-zinc-200 disabled:text-zinc-500">Periksa jawaban</button>
                </div>
            </article>
            <div class="absolute -bottom-10 -left-5 hidden rounded-xl border border-amber-200 bg-white px-4 py-3 shadow-lg sm:block"><p class="text-xs font-semibold text-zinc-500">Untuk komunitas belajar</p><p class="text-sm font-bold text-amber-800">Siswa · Guru · Pengelola</p></div>
        </div>
    </div>
</section>
