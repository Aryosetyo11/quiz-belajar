<section id="peran" class="bg-gradient-to-br from-white via-sky-50/50 to-violet-50/40 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-6">
        <div id="cara-kerja" class="mx-auto mb-10 max-w-2xl text-center" data-reveal><span class="label-eyebrow">Untuk siapa RuangUji?</span><h2 class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-950 sm:text-4xl">Ruang kerja sesuai peran</h2><p class="mt-3 text-base leading-7 text-zinc-600">Setiap akun mendapat akses sesuai tanggung jawabnya. Akun dibuat oleh pengelola, tanpa pendaftaran mandiri.</p></div>
        <div class="mb-6 flex flex-wrap justify-center gap-2" data-role-filter-group role="group" aria-label="Filter peran pengguna">
            @foreach ([['all', 'Semua'], ['student', 'Siswa'], ['teacher', 'Guru'], ['management', 'Pengelola']] as [$filter, $label])
                <button type="button" data-role-filter="{{ $filter }}" aria-pressed="{{ $filter === 'all' ? 'true' : 'false' }}" class="rounded-full border px-4 py-2 text-sm font-semibold transition {{ $filter === 'all' ? 'border-indigo-700 bg-indigo-700 text-white' : 'border-zinc-200 bg-white text-zinc-600 hover:border-indigo-300 hover:text-indigo-700' }}">{{ $label }}</button>
            @endforeach
        </div>
        <p class="sr-only" aria-live="polite" data-role-filter-status>Menampilkan semua peran.</p>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Guru', 'Mengelola kelas dan materi, menyusun bank soal, menjalankan ujian, serta menilai hasil siswa.', 'Email dan kata sandi', 'bg-indigo-50 text-indigo-700', 'G'],
                ['Siswa', 'Membuka materi kelas, mengikuti ujian, dan melihat hasil belajar yang sudah dirilis.', 'NISN dan kata sandi', 'bg-teal-50 text-teal-700', 'S'],
                ['Admin sekolah', 'Mengelola akun, sekolah, rombel, penugasan guru, dan peserta kelas.', 'Akun pengelola', 'bg-violet-50 text-violet-700', 'A'],
                ['Yayasan', 'Melihat ringkasan dan laporan kegiatan dari sekolah-sekolah di bawah yayasan.', 'Akun yayasan', 'bg-amber-50 text-amber-800', 'Y'],
            ] as [$role, $description, $access, $color, $initial])
                @php
                    $filterKey = $role === 'Guru' ? 'teacher' : ($role === 'Siswa' ? 'student' : 'management');
                @endphp
                <article class="flex min-h-56 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm" data-role-card="{{ $filterKey }}" data-reveal>
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $color }} text-lg font-extrabold">{{ $initial }}</span>
                    <h3 class="mt-5 text-base font-bold leading-6 text-zinc-950">{{ $role }}</h3><p class="mt-2 flex-1 text-sm leading-6 text-zinc-600">{{ $description }}</p><span class="mt-4 border-t border-zinc-100 pt-3 text-xs font-semibold text-zinc-500">{{ $access }}</span>
                </article>
            @endforeach
        </div>
        <p class="mt-6 text-center text-sm text-zinc-500">Siswa masuk dengan NISN. Akses guru dan pengelola diberikan melalui jalur internal.</p>
    </div>
</section>
