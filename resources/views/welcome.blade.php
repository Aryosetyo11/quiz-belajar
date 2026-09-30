<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="RuangUji - Sistem Informasi Ujian Digital Berbasis Web Yayasan NC Learning Center dengan Proteksi Real-Time dan Manajemen Akses Terstruktur.">
    <title>RuangUji | Sistem Ujian Digital Yayasan NC Learning Center</title>

    <!-- Google / Bunny Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased selection:bg-indigo-600 selection:text-white">

    <!-- ============================================================== -->
    <!-- HEADER / NAVIGATION -->
    <!-- ============================================================== -->
    <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            
            <!-- Institutional Brand -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm transition-transform duration-200 group-hover:scale-105">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <div>
                    <span class="text-lg font-bold tracking-tight text-slate-900 block leading-tight">RuangUji</span>
                    <span class="text-xs text-slate-500 font-medium">Yayasan NC Learning Center</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600" aria-label="Menu Utama">
                <a href="#keunggulan" class="transition hover:text-indigo-600">Keunggulan</a>
                <a href="#keamanan" class="transition hover:text-indigo-600">Keamanan Ujian</a>
                <a href="#peran" class="transition hover:text-indigo-600">Ruang Akses</a>
                <a href="#profil" class="transition hover:text-indigo-600">Tentang Yayasan</a>
            </nav>

            <!-- Actions -->
            <div class="hidden sm:flex items-center gap-3">
                <button type="button" class="open-portal-btn inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 cursor-pointer">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Masuk Portal
                </button>
            </div>

            <!-- Mobile Menu Toggle -->
            <button id="mobile-menu-btn" type="button" class="md:hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-900" aria-label="Buka Menu" aria-expanded="false">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden border-b border-slate-200 bg-white px-6 py-4 md:hidden">
            <div class="flex flex-col gap-3 text-sm font-medium text-slate-600">
                <a href="#keunggulan" class="py-1 hover:text-indigo-600">Keunggulan</a>
                <a href="#keamanan" class="py-1 hover:text-indigo-600">Keamanan Ujian</a>
                <a href="#peran" class="py-1 hover:text-indigo-600">Ruang Akses</a>
                <a href="#profil" class="py-1 hover:text-indigo-600">Tentang Yayasan</a>
                <div class="pt-3 border-t border-slate-100">
                    <button type="button" class="open-portal-btn w-full rounded-lg bg-indigo-600 py-2.5 text-center text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                        Masuk Portal Ujian
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- ============================================================== -->
        <!-- HERO SECTION -->
        <!-- ============================================================== -->
        <section class="relative overflow-hidden bg-white pt-14 pb-20 md:pt-20 md:pb-28 border-b border-slate-200/60">
            <div class="mx-auto max-w-6xl px-6">
                <div class="grid items-center gap-12 lg:grid-cols-12">
                    
                    <!-- Hero Content -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-100 bg-indigo-50 px-3.5 py-1 text-xs font-medium text-indigo-700">
                            <span class="h-2 w-2 rounded-full bg-indigo-600"></span>
                            <span>Portal Evaluasi Pembelajaran Resmi</span>
                        </div>

                        <!-- Main Title -->
                        <h1 class="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl leading-tight">
                            Evaluasi Belajar Digital yang Tertib, Efisien, dan Berintegritas
                        </h1>

                        <!-- Description -->
                        <p class="text-base text-slate-600 sm:text-lg leading-relaxed">
                            Sistem informasi ujian digital Yayasan NC Learning Center yang dirancang untuk mendukung pelaksanaan kuis dan ujian akademik yang terstruktur, bebas kecurangan, serta memudahkan pendidik dalam pengelolaan penilaian.
                        </p>

                        <!-- Highlights -->
                        <div class="grid grid-cols-2 gap-3 pt-2 text-xs font-medium text-slate-600 sm:grid-cols-3">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Pengawasan Real-Time</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Koreksi Otomatis</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                <span>Hak Akses Terproteksi</span>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="flex flex-wrap items-center gap-4 pt-4">
                            <button type="button" class="open-portal-btn inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 cursor-pointer">
                                <span>Akses Portal Ujian</span>
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </button>
                            <a href="#keamanan" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:text-slate-900">
                                Pelajari Fitur Keamanan
                            </a>
                        </div>
                    </div>

                    <!-- Right Graphic: Clean & Minimal Exam Interface Preview -->
                    <div class="lg:col-span-5">
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-lg shadow-slate-200/50">
                            
                            <!-- Card Top Bar -->
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                    <span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>
                                    <span class="ml-2 text-xs font-medium text-slate-500">Ruang Ujian Siswa</span>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Sesi Terpantau
                                </span>
                            </div>

                            <!-- Exam Info -->
                            <div class="mt-4 rounded-xl bg-slate-50 p-4 border border-slate-100">
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                                    <span>Mata Evaluasi</span>
                                    <span class="font-semibold text-indigo-600">Sisa Waktu: 42 Menit</span>
                                </div>
                                <h3 class="text-sm font-bold text-slate-900">Evaluasi Pembelajaran Terpadu</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Yayasan NC Learning Center • Kelas Evaluasi</p>
                            </div>

                            <!-- Sample Question -->
                            <div class="mt-4 space-y-3">
                                <div class="text-xs font-semibold text-slate-700">
                                    Pertanyaan No. 5 dari 20:
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed bg-white rounded-lg p-3 border border-slate-100">
                                    Bagaimana sistem memastikan integritas pengerjaan kuis saat peserta sedang mengerjakan soal secara daring?
                                </p>
                                
                                <div class="space-y-2 text-xs">
                                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg border border-indigo-200 bg-indigo-50/50 text-indigo-900 font-medium">
                                        <span class="h-4 w-4 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] font-bold">✓</span>
                                        <span>Penguncian layar penuh dan deteksi perpindahan tab browser</span>
                                    </div>
                                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg border border-slate-200 text-slate-600">
                                        <span class="h-4 w-4 rounded-full border border-slate-300 flex items-center justify-center text-[10px]">B</span>
                                        <span>Mengizinkan pengerjaan di luar jendela ujian tanpa peringatan</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Status -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                                <span>Status: Layar Penuh Aktif</span>
                                <span class="font-medium text-emerald-600">Toleransi Pelanggaran: 0 / 3</span>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- NILAI UTAMA (CORE VALUES) -->
        <!-- ============================================================== -->
        <section id="keunggulan" class="py-16 md:py-24">
            <div class="mx-auto max-w-6xl px-6">
                
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-600">Keunggulan Sistem</h2>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Standar Baru Evaluasi Belajar di Lingkungan Yayasan
                    </p>
                    <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">
                        Menggantikan proses manual yang rentan kendala operasional menjadi alur kerja digital yang tertib dan akurat.
                    </p>
                </div>

                <div class="grid gap-6 md:grid-cols-3">
                    
                    <!-- Value 1 -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="h-10 w-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Integritas Terjaga</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Mekanisme proteksi real-time menjaga kejujuran peserta dengan membatasi akses ke sumber luar atau perpindahan jendela selama evaluasi berlangsung.
                        </p>
                    </div>

                    <!-- Value 2 -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="h-10 w-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Efisiensi Pengajar</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pemeriksaan soal pilihan ganda secara otomatis dan pencatatan nilai yang terorganisir menghemat waktu pengajar sehingga dapat lebih fokus pada proses bimbingan.
                        </p>
                    </div>

                    <!-- Value 3 -->
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="h-10 w-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-2">Akses Terstruktur</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pemisahan peran terisolasi antara akun Guru dan Siswa menjamin keamanan kerahasiaan bank soal dan transparansi laporan hasil ujian.
                        </p>
                    </div>

                </div>

            </div>
        </section>

        <!-- ============================================================== -->
        <!-- FITUR KEAMANAN / ANTI-CHEATING -->
        <!-- ============================================================== -->
        <section id="keamanan" class="border-y border-slate-200 bg-white py-16 md:py-24">
            <div class="mx-auto max-w-6xl px-6">
                
                <div class="grid items-center gap-12 lg:grid-cols-12">
                    
                    <div class="lg:col-span-6 space-y-5">
                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Keamanan Ujian Real-Time</span>
                        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                            Pengawasan Cerdas Tanpa Mengganggu Kenyamanan Siswa
                        </h2>
                        <p class="text-sm text-slate-600 leading-relaxed">
                            Sistem dilengkapi modul keamanan terintegrasi pada browser yang secara konsisten memantau kepatuhan peserta selama sesi ujian berlangsung.
                        </p>

                        <div class="space-y-4 pt-2">
                            
                            <div class="flex items-start gap-3.5">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 flex-shrink-0 mt-0.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900">Penguncian Mode Layar Penuh</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Peserta diwajibkan mengerjakan kuis dalam tampilan layar penuh untuk meminimalkan gangguan aplikasi lain.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 flex-shrink-0 mt-0.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900">Deteksi Perpindahan Tab & Jendela</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Mendeteksi seketika saat peserta membuka tab peramban baru atau beralih ke program lain di komputernya.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 flex-shrink-0 mt-0.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900">Pembatasan Salin-Tempel & Menu Konteks</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Menonaktifkan kombinasi tombol salin-tempel dan klik kanan agar kerahasiaan butir soal tetap terjaga.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5">
                                <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 flex-shrink-0 mt-0.5">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900">Peringatan Bertahap & Penyerahan Otomatis</h4>
                                    <p class="text-xs text-slate-500 mt-0.5">Bila peserta melampaui batas toleransi peringatan (maksimal 3 kali), lembar jawaban otomatis terkunci dan terkirim.</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Right Illustration / Overview Card -->
                    <div class="lg:col-span-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-6 sm:p-8">
                            <h3 class="text-base font-bold text-slate-900 mb-1">Mekanisme Pengawasan Terpadu</h3>
                            <p class="text-xs text-slate-500 mb-6">Alur otomatisasi pengamanan sesi ujian siswa</p>

                            <div class="space-y-3 text-xs">
                                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold text-[11px]">1</span>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900">Siswa Masuk Sesi Ujian</p>
                                        <p class="text-slate-500 text-[11px]">Mode layar penuh diaktifkan dan pemantau fokus mulai berjalan.</p>
                                    </div>
                                    <span class="text-emerald-600 font-semibold text-[11px]">Aktif</span>
                                </div>

                                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold text-[11px]">2</span>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900">Pencatatan Anomali Real-Time</p>
                                        <p class="text-slate-500 text-[11px]">Setiap perpindahan tab browser memicu notifikasi peringatan siswa.</p>
                                    </div>
                                    <span class="text-amber-600 font-semibold text-[11px]">Terpantau</span>
                                </div>

                                <div class="flex items-center gap-3 p-3.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold text-[11px]">3</span>
                                    <div class="flex-1">
                                        <p class="font-semibold text-slate-900">Sinkronisasi ke Dashboard Pengajar</p>
                                        <p class="text-slate-500 text-[11px]">Guru pengawas dapat melihat rekapitulasi status kehadiran dan pelanggaran.</p>
                                    </div>
                                    <span class="text-indigo-600 font-semibold text-[11px]">Tercatat</span>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-200 text-center text-xs text-slate-500">
                                Perlindungan berbasis standar keamanan peramban tanpa memerlukan instalasi aplikasi tambahan yang rumit.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ============================================================== -->
        <!-- RUANG AKSES (ROLE-BASED GURU & SISWA) -->
        <!-- ============================================================== -->
        <section id="peran" class="py-16 md:py-24">
            <div class="mx-auto max-w-6xl px-6">
                
                <div class="text-center max-w-2xl mx-auto mb-14">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-indigo-600">Ruang Akses Pengguna</h2>
                    <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                        Didesain Khusus untuk Kebutuhan Guru dan Siswa
                    </p>
                    <p class="mt-3 text-slate-600 text-sm sm:text-base leading-relaxed">
                        Pemisahan fungsi yang jelas memberikan pengalaman yang fokus dan mudah dipahami bagi seluruh sivitas yayasan.
                    </p>
                </div>

                <div class="grid gap-8 md:grid-cols-2">
                    
                    <!-- Card Guru -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Portal Pengajar (Guru)</h3>
                                <p class="text-xs text-slate-500">Manajemen Konten & Pengawasan Ujian</p>
                            </div>
                        </div>

                        <ul class="space-y-3 text-xs text-slate-600">
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Penyusunan Bank Soal:</strong> Mengelola butir soal pilihan ganda dan esai lengkap dengan fasilitas lampiran gambar materi.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Pengaturan Sesi Kuis:</strong> Menentukan durasi waktu pengerjaan, jadwal mulai, dan pengacakan urutan nomor soal.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Pemantauan Langsung:</strong> Memantau progres peserta yang sedang mengerjakan ujian serta catatan pelanggaran siswa.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Laporan Rekapitulasi:</strong> Penilaian instan pilihan ganda, modul periksa esai, dan ekspor lembar rekap nilai ujian.</span>
                            </li>
                        </ul>

                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600">
                                Peran: Pengajar / Administrator Kuis
                            </span>
                        </div>
                    </div>

                    <!-- Card Siswa -->
                    <div class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">Portal Siswa (Peserta)</h3>
                                <p class="text-xs text-slate-500">Pengerjaan Ujian & Evaluasi Hasil</p>
                            </div>
                        </div>

                        <ul class="space-y-3 text-xs text-slate-600">
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Daftar Kuis Aktif:</strong> Menampilkan jadwal ujian yang sedang berlangsung dan kuis yang tersedia untuk dikerjakan.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Antarmuka Pengerjaan Responsif:</strong> Tampilan bersih dan bebas distraksi dengan navigasi nomor soal yang intuitif.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Petunjuk Waktu Real-Time:</strong> Penunjuk sisa waktu yang transparan dan penyimpanan jawaban berkala.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="text-indigo-600 font-bold">•</span>
                                <span><strong>Transparansi Hasil:</strong> Mengetahui pencapaian skor nilai ujian setelah masa evaluasi dinyatakan selesai oleh pengajar.</span>
                            </li>
                        </ul>

                        <div class="mt-6 pt-5 border-t border-slate-100">
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600">
                                Peran: Peserta Ujian
                            </span>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- ============================================================== -->
        <!-- PROFIL YAYASAN NC LEARNING CENTER -->
        <!-- ============================================================== -->
        <section id="profil" class="border-t border-slate-200 bg-white py-16">
            <div class="mx-auto max-w-6xl px-6">
                
                <div class="rounded-2xl border border-slate-200 bg-slate-50/50 p-8 sm:p-10">
                    <div class="grid gap-8 lg:grid-cols-12 items-center">
                        
                        <div class="lg:col-span-7 space-y-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Identitas Lembaga</span>
                            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                                Yayasan NC Learning Center
                            </h2>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Lembaga pendidikan yang senantiasa berkomitmen meningkatkan kualitas proses pembelajaran serta evaluasi peserta didik melalui penerapan teknologi informasi yang aman, efisien, dan berorientasi pada masa depan.
                            </p>

                            <div class="space-y-2 text-xs text-slate-600 pt-2">
                                <div class="flex items-start gap-2">
                                    <svg class="h-4 w-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span>Jl. Turmin Sawangan, Kel. Kedaung, Kec. Sawangan, Kota Depok, Jawa Barat 16516</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    <span>Ketua Yayasan: Dhimas Nurcahya</span>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-5 rounded-xl border border-slate-200 bg-white p-6 shadow-xs">
                            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Pengembangan Sistem</span>
                            <h3 class="text-base font-bold text-slate-900 mt-1">Sutopo Group</h3>
                            <p class="text-xs text-slate-500 mt-1 mb-4 italic">"Building Better Education Through Technology"</p>
                            
                            <p class="text-xs text-slate-600 leading-relaxed mb-4">
                                Dikembangkan sebagai sistem informasi ujian digital terpadu dengan standar keamanan modern berbasis framework web Laravel.
                            </p>

                            <div class="text-[11px] text-slate-500 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <span>Platform CBT RuangUji</span>
                                <span class="font-medium text-indigo-600">Yayasan NC Learning Center</span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        <!-- ============================================================== -->
        <!-- CALL TO ACTION (PORTAL ACCESS) -->
        <!-- ============================================================== -->
        <section class="border-t border-slate-200 bg-white py-16">
            <div class="mx-auto max-w-4xl px-6 text-center">
                <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Mulai Sesi Evaluasi Belajar Digital
                </h2>
                <p class="mt-3 text-slate-600 text-sm sm:text-base max-w-xl mx-auto">
                    Akses portal ujian RuangUji untuk pengajar dan peserta didik di lingkungan Yayasan NC Learning Center.
                </p>
                <div class="mt-6 flex justify-center">
                    <button type="button" class="open-portal-btn inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 cursor-pointer">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        Masuk Portal Ujian
                    </button>
                </div>
            </div>
        </section>

    </main>

    <!-- ============================================================== -->
    <!-- PORTAL MODAL (ELEGANT SELECTION) -->
    <!-- ============================================================== -->
    <div id="portal-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
        <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
            
            <!-- Close Button -->
            <button type="button" class="close-portal-btn absolute top-4 right-4 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 cursor-pointer" aria-label="Tutup">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>

            <!-- Modal Header -->
            <div class="mb-5">
                <h3 class="text-lg font-bold text-slate-900">Portal Masuk RuangUji</h3>
                <p class="text-xs text-slate-500 mt-0.5">Yayasan NC Learning Center</p>
            </div>

            <!-- Role Options -->
            <div class="space-y-3">
                
                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-indigo-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Portal Pengajar (Guru)</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Pengelolaan Soal, Pemantauan, & Nilai</p>
                        </div>
                        <span class="rounded bg-indigo-50 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">Akses Penuh</span>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 transition hover:border-indigo-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-900">Portal Siswa (Peserta)</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Pengerjaan Kuis & Lembar Jawaban</p>
                        </div>
                        <span class="rounded bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-700">Peserta</span>
                    </div>
                </div>

            </div>

            <!-- Status Notice -->
            <div class="mt-5 rounded-lg bg-amber-50 border border-amber-200/80 p-3 text-xs text-amber-800 leading-relaxed">
                <strong>Informasi:</strong> Saat ini halaman berada pada tahap landing page resmi. Ruang pengerjaan ujian dan autentikasi dashboard utama akan dibuka sesuai jadwal pelaksanaan ujian yayasan.
            </div>

            <div class="mt-5 flex justify-end">
                <button type="button" class="close-portal-btn rounded-lg px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                    Tutup
                </button>
            </div>

        </div>
    </div>

    <!-- ============================================================== -->
    <!-- FOOTER -->
    <!-- ============================================================== -->
    <footer class="border-t border-slate-200 bg-slate-50 py-10 text-xs text-slate-500">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-xs">
                        RU
                    </div>
                    <div>
                        <span class="font-bold text-slate-800">RuangUji</span>
                        <span class="text-slate-400 mx-1.5">•</span>
                        <span>Yayasan NC Learning Center</span>
                    </div>
                </div>

                <p class="text-center sm:text-right">
                    &copy; {{ date('Y') }} Yayasan NC Learning Center. Dikembangkan bersama Sutopo Group.
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
