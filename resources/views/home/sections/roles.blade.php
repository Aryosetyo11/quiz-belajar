<section id="peran" class="bg-gradient-to-br from-white via-sky-50/50 to-violet-50/40 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-6">
        <div id="cara-kerja" class="mx-auto mb-10 max-w-2xl text-center" data-reveal><span class="label-eyebrow">Pilih akses</span><h2 class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-950 sm:text-4xl">Satu portal, empat jenis akses</h2><p class="mt-3 text-base leading-7 text-zinc-600">Masuk melalui portal sesuai peran. Setiap akun hanya melihat fitur yang menjadi tanggung jawabnya.</p></div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Guru', 'Kelola kelas dan materi sekarang; fitur kuis dan ujian disiapkan sebagai fokus berikutnya.', 'login.teacher', 'Email dan kata sandi', 'bg-indigo-50 text-indigo-700', 'G'],
                ['Siswa', 'Akses kelas dan materi; pengerjaan kuis dan ujian akan hadir saat modulnya tersedia.', 'login.student', 'NISN dan kata sandi', 'bg-teal-50 text-teal-700', 'S'],
                ['Manajemen All Remote', 'Kelola akun dan akses seluruh portal.', 'login.admin', 'Email dan kata sandi', 'bg-violet-50 text-violet-700', 'R'],
                ['Manajemen Yayasan', 'Pantau ringkasan siswa, guru, dan aktivitas.', 'login.foundation', 'Email dan kata sandi', 'bg-amber-50 text-amber-800', 'Y'],
            ] as [$role, $description, $routeName, $credential, $color, $initial])
                <a href="{{ route($routeName) }}" class="group flex min-h-56 flex-col rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-lg focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200" data-reveal>
                    <div class="flex items-start justify-between"><span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $color }} text-lg font-extrabold">{{ $initial }}</span><span class="text-lg text-zinc-300 transition group-hover:translate-x-1 group-hover:text-indigo-600" aria-hidden="true">&rarr;</span></div>
                    <h3 class="mt-5 text-base font-bold leading-6 text-zinc-950">{{ $role }}</h3><p class="mt-2 flex-1 text-sm leading-6 text-zinc-600">{{ $description }}</p><span class="mt-4 border-t border-zinc-100 pt-3 text-xs font-semibold text-zinc-500">Masuk dengan {{ $credential }}</span>
                </a>
            @endforeach
        </div>
        <p class="mt-6 text-center text-sm text-zinc-500">Akun dibuat oleh pengelola. Pendaftaran mandiri tidak tersedia.</p>
    </div>
</section>
