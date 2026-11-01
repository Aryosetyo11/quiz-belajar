@extends('layouts.portal')

@section('title', 'Mata Pelajaran | RuangUji')

@section('content')
    <header class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Pembelajaran saya</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Mata pelajaran</h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Pilih kelas untuk membuka modul, video, dan materi yang dibagikan pengajar.</p>
        </div>
        @if ($subjects->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 sm:text-right">
                <p class="text-sm font-medium text-slate-600">Materi di kelasmu</p>
                <p class="mt-0.5 text-2xl font-bold text-slate-950">{{ $subjects->sum('materials_count') }} <span class="text-base font-medium text-slate-600">item</span></p>
            </div>
        @endif
    </header>

    @if ($subjects->isNotEmpty())
        <div class="mt-7 grid gap-3 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center">
            <div class="relative min-w-0">
                <label for="subject-search" class="sr-only">Cari mata pelajaran</label>
                <svg class="pointer-events-none absolute left-4 top-1/2 block h-5 w-5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/></svg>
                <input id="subject-search" type="search" placeholder="Cari mata pelajaran atau pengajar..." class="w-full rounded-xl border border-slate-300 bg-white py-3.5 pl-12 pr-4 text-base text-slate-900 placeholder:text-slate-500 focus:border-indigo-600 focus:ring-indigo-600">
            </div>
            <p id="subject-result-count" class="inline-flex w-fit shrink-0 items-center whitespace-nowrap rounded-lg bg-slate-100 px-3.5 py-3 text-base font-semibold text-slate-700 sm:justify-self-end" aria-live="polite">{{ $subjects->count() }} mata pelajaran</p>
        </div>

        <section class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3" id="subject-grid" aria-label="Daftar mata pelajaran">
            @foreach ($subjects as $subject)
                <article class="subject-card flex h-full flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md sm:p-6" data-search="{{ mb_strtolower($subject->name.' '.$subject->teacher->name.' '.($subject->description ?? '')) }}">
                    <div class="flex items-start gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-700" aria-hidden="true">
                            <svg class="block h-6 w-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="break-words text-xl font-bold leading-7 text-slate-950">{{ $subject->name }}</h2>
                            <p class="mt-1 text-base text-slate-700">Pengajar: <span class="font-semibold text-slate-900">{{ $subject->teacher->name }}</span></p>
                            @if ($subject->classroom)<p class="mt-1 text-sm font-medium text-indigo-700">Rombel {{ $subject->classroom->name }} · {{ $subject->classroom->academic_year }}</p>@endif
                        </div>
                    </div>

                    <p class="mt-4 min-h-12 text-base leading-6 text-slate-700">{{ $subject->description ?: 'Belum ada deskripsi untuk kelas ini.' }}</p>

                    @if ($subject->schedules->isNotEmpty())
                        <div class="mt-3 flex flex-wrap gap-2" aria-label="Jadwal mata pelajaran">
                            @foreach ($subject->schedules as $schedule)
                                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700">{{ [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'][$schedule->day_of_week] }} {{ substr($schedule->starts_at, 0, 5) }}–{{ substr($schedule->ends_at, 0, 5) }}</span>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="text-base font-semibold text-slate-900">{{ $subject->materials_count }} materi tersedia</p>
                        @if ($subject->materials_count > 0)
                            <div class="mt-2 flex flex-wrap gap-2 text-sm font-semibold">
                                @if ($subject->documents_count > 0)
                                    <span class="rounded-lg bg-indigo-50 px-3 py-2 text-indigo-900">{{ $subject->documents_count }} modul</span>
                                @endif
                                @if ($subject->videos_count > 0)
                                    <span class="rounded-lg bg-rose-50 px-3 py-2 text-rose-900">{{ $subject->videos_count }} video</span>
                                @endif
                                @if ($subject->links_count > 0)
                                    <span class="rounded-lg bg-emerald-50 px-3 py-2 text-emerald-900">{{ $subject->links_count }} tautan</span>
                                @endif
                            </div>
                        @else
                            <p class="mt-1 text-base text-slate-600">Pengajar belum membagikan materi.</p>
                        @endif
                    </div>

                    <a href="{{ route('student.subjects.show', $subject) }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-700 px-4 py-3 text-base font-semibold text-white transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">
                        Buka kelas
                        <svg class="block h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    </a>
                </article>
            @endforeach
        </section>

        <div id="subject-search-empty" class="mt-4 hidden rounded-xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center" role="status">
            <h2 class="text-lg font-semibold text-slate-900">Mata pelajaran tidak ditemukan</h2>
            <p class="mt-1 text-base text-slate-700">Coba kata kunci nama kelas atau pengajar yang lain.</p>
        </div>

        <script>
            (function () {
                const searchInput = document.getElementById('subject-search');
                const cards = [...document.querySelectorAll('.subject-card')];
                const emptyState = document.getElementById('subject-search-empty');
                const resultCount = document.getElementById('subject-result-count');

                searchInput?.addEventListener('input', function () {
                    const query = this.value.trim().toLocaleLowerCase();
                    let visibleCount = 0;

                    cards.forEach(function (card) {
                        const matches = card.dataset.search.includes(query);
                        card.classList.toggle('hidden', !matches);
                        visibleCount += matches ? 1 : 0;
                    });

                    emptyState?.classList.toggle('hidden', visibleCount > 0);
                    if (resultCount) {
                        resultCount.textContent = query
                            ? `${visibleCount} dari ${cards.length} mata pelajaran`
                            : `${cards.length} mata pelajaran`;
                    }
                });
            })();
        </script>
    @else
        <section class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-8" aria-label="Belum ada mata pelajaran">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-700">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            </span>
            <h2 class="mt-4 text-xl font-semibold text-slate-950">Belum ada mata pelajaran</h2>
            <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Kelas akan muncul di sini setelah pengajar mendaftarkan akunmu. Pastikan NISN di profil sudah benar, lalu hubungi pengajar bila kelas belum terlihat.</p>
            <a href="{{ route('profile.edit') }}" class="mt-5 inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Periksa profil</a>
        </section>
    @endif
@endsection
