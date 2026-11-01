@extends('layouts.portal')

@section('title', 'Pemantauan | RuangUji')

@section('content')
    <div class="space-y-7">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">Panel Pengajar</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Pemantauan ujian</h1>
                <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Pantau pengerjaan yang masih berjalan dan tinjau catatan pelanggaran dari sistem proctoring.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span id="monitoring-updated-at" class="text-xs text-slate-500" aria-live="polite">Diperbarui {{ now()->format('H:i:s') }}</span>
                <button type="button" id="refresh-monitoring" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Perbarui</button>
                <a href="{{ route('teacher.exam-sessions') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Buka sesi ujian</a>
            </div>
        </header>

        <form method="GET" action="{{ route('teacher.monitoring') }}" class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-end">
            <div class="min-w-0 flex-1">
                <label for="exam-filter" class="mb-1.5 block text-sm font-semibold text-slate-800">Tampilkan sesi</label>
                <select id="exam-filter" name="exam_id" class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-800">
                    <option value="">Semua sesi ujian</option>
                    @foreach ($sessions as $session)
                        <option value="{{ $session->id }}" @selected($selectedExamId === $session->id)>{{ $session->subject->name }} · {{ $session->title }}{{ $session->is_published ? '' : ' (Draf)' }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Terapkan filter</button>
            @if ($selectedExamId !== null)
                <a href="{{ route('teacher.monitoring') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            @endif
        </form>

        <section id="monitoring-summary" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan pemantauan">
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm font-medium text-slate-600">Pengerjaan aktif</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $activeCount }}</p>
            </article>
            <article class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-sm font-medium text-amber-900">Aktif dengan catatan</p>
                <p class="mt-2 text-3xl font-bold text-amber-950">{{ $flaggedActiveCount }}</p>
            </article>
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm font-medium text-slate-600">Dikumpulkan hari ini</p>
                <p class="mt-2 text-3xl font-bold text-slate-950">{{ $submittedTodayCount }}</p>
            </article>
            <article class="rounded-xl border border-rose-200 bg-rose-50 p-5">
                <a href="#needs-attention" class="block rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-rose-600">
                    <p class="text-sm font-semibold text-rose-900">Perlu tindak lanjut</p>
                    <p class="mt-2 text-3xl font-bold text-rose-950">{{ $needsAttentionAttempts->count() }}</p>
                    <p class="mt-1 text-xs text-rose-800">Siswa menunggu akses dibuka</p>
                </a>
            </article>
        </section>

        <section id="needs-attention" class="overflow-hidden rounded-xl border border-rose-200 bg-white" aria-labelledby="attention-heading">
            <div class="border-b border-rose-100 bg-rose-50 px-5 py-4 sm:px-6">
                <h2 id="attention-heading" class="text-xl font-semibold text-rose-950">Perlu tindak lanjut</h2>
                <p class="mt-1 text-sm text-rose-900">Siswa yang ujiannya dihentikan otomatis dan menunggu keputusan guru.</p>
            </div>

            @if ($needsAttentionAttempts->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach ($needsAttentionAttempts as $attempt)
                        <article class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <div class="min-w-0">
                                <h3 class="text-lg font-semibold text-slate-950">{{ $attempt->student->name ?? 'Siswa' }}</h3>
                                <p class="mt-1 text-sm text-slate-600">{{ $attempt->exam->subject->name }} · {{ $attempt->exam->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">Dihentikan {{ $attempt->submitted_at->diffForHumans() }} · {{ count($attempt->violation_logs ?? []) }} catatan pelanggaran</p>
                                @if ($attempt->monitoringLogs->isNotEmpty())
                                    <details class="mt-2 w-fit rounded-lg border border-rose-100 bg-rose-50/60 px-3 py-2">
                                        <summary class="cursor-pointer text-sm font-semibold text-rose-800">Lihat catatan terakhir</summary>
                                        <p class="mt-2 max-w-xl text-sm leading-6 text-rose-900">{{ $attempt->monitoringLogs->first()['reason'] }}</p>
                                    </details>
                                @endif
                            </div>
                            @if ($attempt->exam->is_published)
                                <button type="button" onclick="document.getElementById('restore-access-{{ $attempt->id }}').showModal()" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tinjau &amp; buka akses</button>
                            @else
                                <span class="text-sm font-medium text-slate-600">Publikasikan sesi untuk membuka akses</span>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="px-5 py-8 text-center text-sm text-slate-600 sm:px-6">Tidak ada siswa yang menunggu akses kembali.</p>
            @endif
        </section>

        <section id="active-monitoring" class="overflow-hidden rounded-xl border border-slate-200 bg-white" aria-labelledby="active-heading">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="active-heading" class="text-xl font-semibold text-slate-950">Sedang dikerjakan</h2>
                <p class="mt-1 text-sm text-slate-600">Status dan pelanggaran diperbarui otomatis setiap 15 detik.</p>
            </div>

            @if ($activeAttempts->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach ($activeAttempts as $attempt)
                        <article class="px-5 py-5 sm:px-6">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-semibold text-slate-950">{{ $attempt->student->name ?? 'Siswa' }}</h3>
                                        @if ($attempt->monitoringExpired)
                                            <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-800">Waktu habis</span>
                                        @else
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Berlangsung</span>
                                        @endif
                                        @if (!empty($attempt->access_restorations))
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Akses dibuka kembali</span>
                                        @endif
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $attempt->exam->enable_proctoring ? 'bg-indigo-50 text-indigo-800' : 'bg-slate-100 text-slate-700' }}">Proctoring {{ $attempt->exam->enable_proctoring ? 'aktif' : 'nonaktif' }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ $attempt->student->nisn ? 'NISN '.$attempt->student->nisn.' · ' : '' }}{{ $attempt->exam->subject->name }} · {{ $attempt->exam->title }}</p>
                                    <p class="mt-2 text-sm text-slate-500">Mulai {{ $attempt->started_at->format('d M Y, H:i') }} · Batas waktu {{ $attempt->monitoringDeadline->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2 rounded-lg px-3 py-2 {{ $attempt->violation_count > 0 ? 'bg-amber-50 text-amber-900' : 'bg-slate-50 text-slate-700' }}">
                                    <span class="text-sm font-semibold">Pelanggaran</span>
                                    <span class="text-lg font-bold">{{ $attempt->violation_count ?? 0 }}/{{ $attempt->exam->max_violations ?? 3 }}</span>
                                </div>
                            </div>

                            @if (!empty($attempt->violation_logs))
                                <details class="mt-4 rounded-lg border border-slate-200">
                                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-800">Lihat {{ count($attempt->violation_logs) }} catatan aktivitas</summary>
                                    <ul class="space-y-2 border-t border-slate-100 px-4 py-3">
                                        @foreach (collect($attempt->violation_logs)->sortByDesc('time') as $log)
                                            <li class="flex flex-col gap-1 text-sm sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                                <span class="text-slate-700">{{ $log['reason'] ?? 'Aktivitas tercatat' }}</span>
                                                <time class="shrink-0 text-xs text-slate-500">{{ isset($log['time']) ? \Illuminate\Support\Carbon::parse($log['time'])->format('d M Y, H:i:s') : 'Waktu tidak tersedia' }}</time>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @else
                                <p class="mt-3 text-sm text-slate-500">Belum ada pelanggaran yang tercatat.</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-12 text-center sm:px-6">
                    <h3 class="text-lg font-semibold text-slate-900">Tidak ada pengerjaan aktif</h3>
                    <p class="mt-1 text-sm text-slate-600">Saat siswa mulai ujian, pengerjaan mereka akan muncul di sini.</p>
                </div>
            @endif
        </section>

        <section id="monitoring-history" class="overflow-hidden rounded-xl border border-slate-200 bg-white" aria-labelledby="activity-heading">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h2 id="activity-heading" class="text-xl font-semibold text-slate-950">Riwayat catatan proctoring</h2>
                <p class="mt-1 text-sm text-slate-600">Maksimal 30 pengerjaan terakhir yang memiliki catatan atau dihentikan otomatis.</p>
            </div>

            @if ($flaggedAttempts->isNotEmpty())
                <div class="divide-y divide-slate-100">
                    @foreach ($flaggedAttempts as $attempt)
                        <article class="px-5 py-4 sm:px-6">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-900">{{ $attempt->student->name ?? 'Siswa' }} <span class="font-normal text-slate-500">· {{ $attempt->exam->subject->name }} · {{ $attempt->exam->title }}</span></p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $attempt->submitted_at ? 'Dikumpulkan '.$attempt->submitted_at->format('d M Y, H:i') : 'Masih berlangsung' }} · {{ $attempt->violation_count }} pelanggaran</p>
                                </div>
                                @if ($attempt->is_terminated_by_system && $attempt->submitted_at !== null)
                                    <span class="w-fit rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-800">Dihentikan otomatis</span>
                                @elseif ($attempt->is_terminated_by_system)
                                    <span class="w-fit rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Akses dibuka kembali</span>
                                @elseif (!empty($attempt->access_restorations))
                                    <span class="w-fit rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800">Pernah dibuka kembali</span>
                                @endif
                            </div>
                            @if ($attempt->monitoringLogs->isNotEmpty())
                                <details class="mt-3 rounded-lg border border-slate-200">
                                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Lihat {{ $attempt->monitoringLogs->count() }} catatan aktivitas</summary>
                                    <ul class="space-y-2 border-t border-slate-100 px-4 py-3">
                                        @foreach ($attempt->monitoringLogs as $log)
                                            <li class="flex flex-col gap-1 text-sm sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                                <span class="text-slate-700">{{ $log['reason'] }}</span>
                                                <time class="shrink-0 text-xs text-slate-500">{{ $log['time']->format('d M Y, H:i:s') }}</time>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif

                            @if (!empty($attempt->access_restorations))
                                <details class="mt-3 rounded-lg border border-emerald-200 bg-emerald-50/50">
                                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-emerald-900">Riwayat akses yang diberikan guru ({{ count($attempt->access_restorations) }})</summary>
                                    <ul class="space-y-2 border-t border-emerald-100 px-4 py-3">
                                        @foreach (collect($attempt->access_restorations)->sortByDesc('granted_at') as $restoration)
                                            <li class="text-sm text-slate-700">
                                                <span class="font-semibold">{{ isset($restoration['granted_at']) ? \Illuminate\Support\Carbon::parse($restoration['granted_at'])->format('d M Y, H:i') : 'Waktu tidak tersedia' }}</span>
                                                · +{{ $restoration['extra_time_minutes'] ?? 0 }} menit
                                                · {{ $restoration['reason'] ?? 'Alasan tidak tersedia' }}
                                                · Oleh {{ $restoration['granted_by_name'] ?? 'guru' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif

                            @if ($attempt->is_terminated_by_system && $attempt->submitted_at !== null && $attempt->exam->is_published)
                                <dialog id="restore-access-{{ $attempt->id }}" class="fixed left-1/2 top-1/2 m-0 w-[calc(100%-2rem)] max-w-lg -translate-x-1/2 -translate-y-1/2 rounded-2xl border border-slate-200 p-0 shadow-xl backdrop:bg-slate-950/50">
                                    <form method="POST" action="{{ route('teacher.monitoring.restore-access', $attempt) }}" class="space-y-5 p-6">
                                        @csrf
                                        <div>
                                            <h3 class="text-xl font-semibold text-slate-950">Buka akses siswa kembali</h3>
                                            <p class="mt-2 text-sm leading-6 text-slate-600">Paket soal dan jawaban yang sudah tersimpan akan dipertahankan. Riwayat pelanggaran tidak dihapus; jatah pelanggaran dimulai ulang untuk akses berikutnya.</p>
                                        </div>
                                        <div>
                                            <label for="restore-minutes-{{ $attempt->id }}" class="mb-1.5 block text-sm font-semibold text-slate-800">Tambahan waktu (menit)</label>
                                            <input id="restore-minutes-{{ $attempt->id }}" type="number" name="extra_time_minutes" value="15" min="1" max="300" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-base">
                                            <p class="mt-1 text-xs text-slate-500">Waktu ini ditambahkan ke waktu yang masih tersisa. Jika waktu ujian sudah habis, hitung mundur baru dimulai saat akses dibuka.</p>
                                        </div>
                                        <div>
                                            <label for="restore-reason-{{ $attempt->id }}" class="mb-1.5 block text-sm font-semibold text-slate-800">Alasan membuka akses</label>
                                            <textarea id="restore-reason-{{ $attempt->id }}" name="reason" rows="3" minlength="5" maxlength="500" required class="block w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Contoh: siswa mengalami gangguan jaringan"></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                                            <button type="button" onclick="document.getElementById('restore-access-{{ $attempt->id }}').close()" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                                            <button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">Buka akses</button>
                                        </div>
                                    </form>
                                </dialog>
                            @elseif ($attempt->is_terminated_by_system && $attempt->submitted_at !== null)
                                <p class="mt-3 text-sm text-slate-600">Publikasikan sesi ujian kembali untuk membuka akses siswa.</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <p class="px-5 py-8 text-center text-sm text-slate-600 sm:px-6">Belum ada catatan pelanggaran pada sesi yang dipilih.</p>
            @endif
        </section>

        <p class="text-xs leading-5 text-slate-500">Pemantauan ini menampilkan peristiwa yang dikirim oleh browser siswa, seperti keluar dari layar penuh atau berpindah tab. Ini bukan pengawasan kamera.</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const refreshButton = document.getElementById('refresh-monitoring');
            const updatedAt = document.getElementById('monitoring-updated-at');
            const sections = ['monitoring-summary', 'needs-attention', 'active-monitoring', 'monitoring-history'];

            const refreshMonitoring = async () => {
                if (document.querySelector('dialog[open]')) return;

                if (refreshButton) {
                    refreshButton.disabled = true;
                    refreshButton.textContent = 'Memperbarui…';
                }

                try {
                    const response = await fetch(window.location.href, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        cache: 'no-store',
                    });
                    if (!response.ok) throw new Error('Pembaruan gagal.');

                    const html = await response.text();
                    const refreshedDocument = new DOMParser().parseFromString(html, 'text/html');
                    sections.forEach((sectionId) => {
                        const currentSection = document.getElementById(sectionId);
                        const refreshedSection = refreshedDocument.getElementById(sectionId);
                        if (currentSection && refreshedSection) currentSection.replaceWith(refreshedSection);
                    });

                    if (updatedAt) updatedAt.textContent = `Diperbarui ${new Date().toLocaleTimeString('id-ID')}`;
                } catch (error) {
                    if (updatedAt) updatedAt.textContent = 'Pembaruan gagal; data terakhir tetap ditampilkan.';
                } finally {
                    if (refreshButton) {
                        refreshButton.disabled = false;
                        refreshButton.textContent = 'Perbarui';
                    }
                }
            };

            refreshButton?.addEventListener('click', refreshMonitoring);
            window.setInterval(refreshMonitoring, 15000);
        });
    </script>
@endsection
