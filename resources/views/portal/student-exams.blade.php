@extends('layouts.portal')

@section('title', 'Ujian Saya | RuangUji')

@section('content')
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Evaluasi pembelajaran</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Ujian saya</h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Cek ujian yang perlu dikerjakan, jadwal pembukaan, dan hasil yang sudah keluar.</p>
        </div>
        <a href="{{ route('student.subjects') }}" class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            Buka materi belajar
        </a>
    </header>

    @if ($errors->has('exam'))
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-base text-amber-900" role="alert">{{ $errors->first('exam') }}</div>
    @endif

    <section class="mt-7 grid gap-3 sm:grid-cols-3" aria-label="Ringkasan ujian">
        <article class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-4">
            <p class="text-sm font-semibold text-indigo-800">Perlu dikerjakan</p>
            <p class="mt-1 text-2xl font-bold text-slate-950">{{ ($statusCounts->get('ongoing', 0) + $statusCounts->get('available', 0)) }}</p>
            <p class="mt-1 text-sm text-slate-700">Tersedia atau sedang berjalan</p>
        </article>
        <article class="rounded-xl border border-blue-100 bg-blue-50/70 p-4">
            <p class="text-sm font-semibold text-blue-800">Terjadwal</p>
            <p class="mt-1 text-2xl font-bold text-slate-950">{{ $statusCounts->get('scheduled', 0) }}</p>
            <p class="mt-1 text-sm text-slate-700">Belum dibuka oleh pengajar</p>
        </article>
        <article class="rounded-xl border border-emerald-100 bg-emerald-50/70 p-4">
            <p class="text-sm font-semibold text-emerald-800">Selesai</p>
            <p class="mt-1 text-2xl font-bold text-slate-950">{{ $statusCounts->get('completed', 0) }}</p>
            <p class="mt-1 text-sm text-slate-700">Jawaban sudah dikirim</p>
        </article>
    </section>

    @if ($exams->isNotEmpty())
        <div class="mt-7 flex flex-wrap gap-2" id="exam-filters" aria-label="Filter ujian">
            <button type="button" data-exam-filter="all" aria-pressed="true" class="exam-filter rounded-full bg-indigo-700 px-4 py-2 text-sm font-semibold text-white transition">Semua <span class="ml-1 rounded-full bg-white/20 px-2 py-0.5 text-xs">{{ $exams->count() }}</span></button>
            <button type="button" data-exam-filter="actionable" aria-pressed="false" class="exam-filter rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Perlu dikerjakan <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $statusCounts->get('ongoing', 0) + $statusCounts->get('available', 0) }}</span></button>
            <button type="button" data-exam-filter="scheduled" aria-pressed="false" class="exam-filter rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Terjadwal <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $statusCounts->get('scheduled', 0) }}</span></button>
            <button type="button" data-exam-filter="completed" aria-pressed="false" class="exam-filter rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Selesai <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $statusCounts->get('completed', 0) }}</span></button>
            @if ($statusCounts->get('expired', 0) > 0)
                <button type="button" data-exam-filter="expired" aria-pressed="false" class="exam-filter rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Berakhir <span class="ml-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $statusCounts->get('expired', 0) }}</span></button>
            @endif
        </div>

        <section class="mt-4 grid gap-4 md:grid-cols-2" id="exam-list" aria-label="Daftar ujian">
            @foreach ($exams as $exam)
                @php($attempt = $exam->attempts->first())
                @php($status = $exam->student_status)
                <article class="exam-card flex h-full flex-col rounded-2xl border bg-white p-5 shadow-sm transition duration-200 hover:shadow-md sm:p-6 {{ $status === 'completed' ? 'border-emerald-200' : ($status === 'ongoing' ? 'border-amber-200' : ($status === 'expired' ? 'border-slate-200' : 'border-slate-200')) }}" data-exam-status="{{ $status }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-indigo-700">{{ $exam->subject->name }}</p>
                            <h2 class="mt-1 break-words text-xl font-bold leading-7 text-slate-950">{{ $exam->title }}</h2>
                        </div>
                        @if ($status === 'completed')
                            <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-semibold text-emerald-900">Selesai</span>
                        @elseif ($status === 'ongoing')
                            <span class="shrink-0 rounded-full bg-amber-100 px-3 py-1.5 text-sm font-semibold text-amber-900">Sedang dikerjakan</span>
                        @elseif ($status === 'scheduled')
                            <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1.5 text-sm font-semibold text-blue-900">Terjadwal</span>
                        @elseif ($status === 'expired')
                            <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700">Berakhir</span>
                        @else
                            <span class="shrink-0 rounded-full bg-indigo-100 px-3 py-1.5 text-sm font-semibold text-indigo-900">Tersedia</span>
                        @endif
                    </div>

                    @if ($exam->description)
                        <p class="mt-3 text-base leading-6 text-slate-700">{{ Str::limit($exam->description, 140) }}</p>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-sm text-slate-700">
                        <span>{{ $exam->question_count ?? $exam->questions_count }} soal</span>
                        <span>{{ $exam->duration_minutes }} menit</span>
                        @if ($exam->starts_at)
                            <span>{{ $status === 'scheduled' ? 'Dibuka' : 'Mulai' }} {{ $exam->starts_at->format('d M Y, H:i') }}</span>
                        @endif
                        @if ($exam->ends_at)
                            <span>Batas akhir {{ $exam->ends_at->format('d M Y, H:i') }}</span>
                        @endif
                    </div>

                    @if ($status === 'completed' && $attempt)
                        <div class="mt-4 rounded-xl bg-emerald-50 px-4 py-3">
                            <p class="text-sm font-semibold text-emerald-900">Hasil ujian</p>
                            @if ($attempt->score !== null)
                                <p class="mt-0.5 text-2xl font-bold text-emerald-900">{{ $attempt->score }}<span class="text-base font-medium text-emerald-800">/100</span></p>
                            @else
                                <p class="mt-1 text-base font-semibold text-amber-900">Esai menunggu penilaian pengajar</p>
                            @endif
                        </div>
                    @elseif ($status === 'ongoing')
                        <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm leading-5 text-amber-900">Kamu sudah memulai ujian ini. Lanjutkan untuk menyelesaikannya sebelum waktunya habis.</p>
                    @elseif ($status === 'expired')
                        <p class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm leading-5 text-slate-700">Masa pengerjaan ujian ini sudah berakhir. Hubungi pengajar jika kamu memerlukan tindak lanjut.</p>
                    @endif

                    <div class="mt-auto pt-5">
                        @if ($status === 'completed')
                            <a href="{{ route('student.exams.show', $exam) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-300 px-4 py-3 text-base font-semibold text-slate-800 transition hover:bg-slate-50">Lihat hasil</a>
                        @elseif ($status === 'ongoing')
                            <a href="{{ route('student.exams.show', $exam) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-amber-700 px-4 py-3 text-base font-semibold text-white transition hover:bg-amber-800">Lanjutkan ujian</a>
                        @elseif ($status === 'available')
                            <a href="{{ route('student.exams.show', $exam) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-indigo-700 px-4 py-3 text-base font-semibold text-white transition hover:bg-indigo-800">Lihat instruksi</a>
                        @elseif ($status === 'scheduled')
                            <button type="button" disabled class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-4 py-3 text-base font-semibold text-slate-500">Menunggu jadwal dibuka</button>
                        @else
                            <button type="button" disabled class="w-full cursor-not-allowed rounded-xl bg-slate-100 px-4 py-3 text-base font-semibold text-slate-500">Ujian sudah berakhir</button>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        <div id="exam-filter-empty" class="mt-4 hidden rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center" role="status">
            <h2 class="text-lg font-semibold text-slate-900">Belum ada ujian pada kategori ini</h2>
            <p class="mt-1 text-base text-slate-600">Pilih kategori lain untuk melihat ujianmu.</p>
        </div>

        <script>
            (function () {
                const filters = [...document.querySelectorAll('[data-exam-filter]')];
                const cards = [...document.querySelectorAll('.exam-card')];
                const emptyState = document.getElementById('exam-filter-empty');

                filters.forEach(function (filterButton) {
                    filterButton.addEventListener('click', function () {
                        const selectedFilter = this.dataset.examFilter;
                        let visibleCount = 0;

                        filters.forEach(function (button) {
                            const isSelected = button === filterButton;
                            button.setAttribute('aria-pressed', String(isSelected));
                            button.classList.toggle('bg-indigo-700', isSelected);
                            button.classList.toggle('text-white', isSelected);
                            button.classList.toggle('border-slate-200', !isSelected);
                            button.classList.toggle('bg-white', !isSelected);
                            button.classList.toggle('text-slate-700', !isSelected);
                        });

                        cards.forEach(function (card) {
                            const status = card.dataset.examStatus;
                            const matches = selectedFilter === 'all'
                                || (selectedFilter === 'actionable' && ['ongoing', 'available'].includes(status))
                                || status === selectedFilter;

                            card.classList.toggle('hidden', !matches);
                            visibleCount += matches ? 1 : 0;
                        });

                        emptyState?.classList.toggle('hidden', visibleCount > 0);
                    });
                });
            })();
        </script>
    @else
        <section class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-8" aria-label="Belum ada ujian">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700" aria-hidden="true">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
            </span>
            <h2 class="mt-4 text-xl font-semibold text-slate-950">Belum ada ujian</h2>
            <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Ujian akan muncul di sini setelah pengajar mempublikasikannya. Sambil menunggu, kamu bisa membaca materi di mata pelajaran.</p>
            <a href="{{ route('student.subjects') }}" class="mt-5 inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-3 text-base font-semibold text-white transition hover:bg-indigo-800">Buka mata pelajaran</a>
        </section>
    @endif
@endsection
