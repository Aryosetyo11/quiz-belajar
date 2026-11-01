<section id="faq" class="border-t border-zinc-200/80 bg-zinc-50/60 py-16 sm:py-20">
    <div class="mx-auto max-w-4xl px-6">
        <div class="mx-auto mb-12 max-w-2xl text-center" data-reveal>
            <span class="label-eyebrow">Pertanyaan Umum</span>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-zinc-950 sm:text-4xl">
                Sering Ditanyakan
            </h2>
            <p class="mt-3 text-base leading-7 text-zinc-600">
                Punya pertanyaan seputar akun, pelaksanaan ujian, atau kendala teknis? Temukan jawabannya di bawah ini.
            </p>
        </div>

        <div class="space-y-3.5" data-reveal>
            @php
                $faqs = [
                    [
                        'question' => 'Bagaimana cara mendapatkan akun untuk masuk ke RuangUji?',
                        'answer' => 'Siswa masuk menggunakan NISN dan kata sandi dari sekolah. Guru dan pengelola menggunakan halaman login khusus serta akun yang diberikan secara internal.',
                    ],
                    [
                        'question' => 'Apakah kuis dan ujian bisa diakses melalui smartphone?',
                        'answer' => 'Materi dapat dibuka melalui peramban web. Untuk ujian, gunakan perangkat dan peramban yang diarahkan oleh guru agar fitur ujian berjalan sesuai ketentuan.',
                    ],
                    [
                        'question' => 'Bagaimana jika koneksi internet terputus saat ujian berlangsung?',
                        'answer' => 'Jawaban disimpan secara berkala. Sambungkan kembali perangkat ke internet dan ikuti petunjuk pada halaman ujian. Jika sesi tidak pulih, segera hubungi guru pengawas.',
                    ],
                    [
                        'question' => 'Apa yang harus dilakukan jika lupa kata sandi?',
                        'answer' => 'Demi keamanan data akademik, silakan hubungi Guru pengampu atau Administrator sekolah untuk meminta pengaturan ulang (reset) kata sandi akun Anda.',
                    ],
                    [
                        'question' => 'Kapan hasil dan nilai kuis dapat dilihat oleh siswa?',
                        'answer' => 'Guru menentukan kapan hasil ditampilkan. Nilai dapat menunggu pemeriksaan jawaban esai atau rilis dari guru.',
                    ],
                    [
                        'question' => 'Apakah materi belajar dari guru bisa diunduh untuk belajar offline?',
                        'answer' => 'Berkas yang diunggah guru, seperti PDF, dapat diunduh oleh siswa yang terdaftar di kelas. Tautan Drive atau Canva mengikuti pengaturan berbagi dari pemiliknya.',
                    ],
                ];
            @endphp

            @foreach ($faqs as $faq)
                <details class="group rounded-2xl border border-zinc-200/90 bg-white p-5 shadow-xs transition duration-200 hover:border-indigo-200 hover:shadow-sm">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-zinc-900 select-none">
                        <span class="text-base text-zinc-900 group-hover:text-indigo-600 transition-colors">
                            {{ $faq['question'] }}
                        </span>
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-zinc-500 transition-transform duration-300 group-open:rotate-180 group-open:bg-indigo-50 group-open:text-indigo-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </span>
                    </summary>
                    <p class="mt-3.5 border-t border-zinc-100 pt-3.5 text-sm leading-6 text-zinc-600">
                        {{ $faq['answer'] }}
                    </p>
                </details>
            @endforeach
        </div>

        <div class="mt-10 rounded-2xl border border-dashed border-zinc-300 bg-white p-6 text-center sm:flex sm:items-center sm:justify-between sm:text-left" data-reveal>
            <div>
                <h3 class="text-sm font-bold text-zinc-900">Butuh bantuan masuk?</h3>
                <p class="mt-0.5 text-xs text-zinc-500">Hubungi guru atau administrator sekolah untuk akun dan bantuan teknis.</p>
            </div>
        </div>
    </div>
</section>
