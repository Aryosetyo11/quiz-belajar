@extends('layouts.portal')

@section('title', 'Materi '.$subject->name.' | RuangUji')

@section('content')
    <a href="{{ route('student.subjects') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 transition hover:text-indigo-700">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Kembali ke mata pelajaran
    </a>
    <header class="mt-5">
        <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Pembelajaran saya</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $subject->name }}</h1>
        <p class="mt-2 text-base text-slate-600">Pengajar: <span class="font-semibold text-slate-800">{{ $subject->teacher->name }}</span></p>
        @if ($subject->classroom)<p class="mt-1 text-sm font-medium text-indigo-700">Rombel {{ $subject->classroom->name }} · {{ $subject->classroom->academic_year }}</p>@endif
        @if ($subject->schedules->isNotEmpty())
            <div class="mt-3 flex flex-wrap gap-2" aria-label="Jadwal mata pelajaran">
                @foreach ($subject->schedules as $schedule)
                    <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700">{{ [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$schedule->day_of_week] }} · {{ substr($schedule->starts_at, 0, 5) }}–{{ substr($schedule->ends_at, 0, 5) }} @if ($schedule->location)· {{ $schedule->location }}@endif</span>
                @endforeach
            </div>
        @endif
    </header>

    @if ($materials->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Materi belum ditambahkan</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600">Pengajar belum menambahkan materi untuk mata pelajaran ini.</p>
        </div>
    @else
        <section class="mt-6 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5" aria-label="Panduan belajar">
            <div>
                <h2 class="text-base font-bold text-slate-900">Pilih cara belajar yang cocok</h2>
                <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">Gunakan modul untuk membaca dan mengulang materi. Tonton video untuk memahami penjelasan, lalu kembali ke modul bila ingin mencatat poin penting.</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2 text-sm font-semibold">
                <span class="rounded-lg bg-indigo-50 px-3 py-2 text-indigo-800">{{ $materials->filter(fn ($material) => $material->type === 'file')->count() }} dokumen</span>
                <span class="rounded-lg bg-rose-50 px-3 py-2 text-rose-800">{{ $materials->filter(fn ($material) => $material->isVideo())->count() }} video</span>
                <span class="rounded-lg bg-emerald-50 px-3 py-2 text-emerald-800">{{ $materials->filter(fn ($material) => $material->isExternalLink())->count() }} tautan</span>
            </div>
        </section>

        {{-- Filter Tabs --}}
        <div class="mt-6 flex items-center gap-2" id="material-filter" data-filter-active="all">
            <button type="button" data-filter="all" class="rounded-full bg-indigo-700 px-4 py-2 text-sm font-semibold text-white transition">Semua <span class="ml-1 rounded-full bg-white/20 px-1.5 py-0.5 text-xs">{{ $materials->count() }}</span></button>
            <button type="button" data-filter="video" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-700">
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z"/></svg>
                    Video <span class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-xs">{{ $materials->filter(fn ($m) => $m->isVideo())->count() }}</span>
                </span>
            </button>
            <button type="button" data-filter="document" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700">
                <span class="flex items-center gap-1.5">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                    Dokumen <span class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-xs">{{ $materials->filter(fn ($material) => $material->type === 'file')->count() }}</span>
                </span>
            </button>
            <button type="button" data-filter="link" class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700">
                Tautan <span class="ml-1 rounded-full bg-slate-100 px-1.5 py-0.5 text-xs">{{ $materials->filter(fn ($material) => $material->isExternalLink())->count() }}</span>
            </button>
        </div>

        {{-- 2-column layout: sidebar + viewer --}}
        <div class="mt-4 lg:grid lg:grid-cols-[280px_minmax(0,1fr)] lg:gap-6" id="materials-layout">
            {{-- Sidebar: material list --}}
            <aside class="mb-4 lg:mb-0">
                <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                    <div class="border-b border-slate-100 px-4 py-3">
                        <h2 class="text-sm font-bold text-slate-900">Daftar materi</h2>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $materials->count() }} materi tersedia</p>
                    </div>
                    <ul class="divide-y divide-slate-100" id="material-list">
                        @foreach ($materials as $index => $material)
                            <li data-material-type="{{ $material->isVideo() ? 'video' : ($material->isExternalLink() ? 'link' : 'document') }}">
                                <button
                                    type="button"
                                    data-material-id="{{ $index }}"
                                    class="material-tab flex w-full items-start gap-3 px-4 py-3.5 text-left transition hover:bg-slate-50 {{ $index === 0 ? 'bg-indigo-50' : '' }}"
                                    aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                                >
                                    @if ($material->isVideo())
                                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 0 1 0 .656l-5.603 3.113a.375.375 0 0 1-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112Z"/></svg>
                                        </span>
                                    @elseif ($material->isExternalLink())
                                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 13a5 5 0 0 0 7.07 0l3-3A5 5 0 0 0 13 2.93l-1.72 1.71M14 11a5 5 0 0 0-7.07 0l-3 3A5 5 0 0 0 11 21.07l1.71-1.71"/></svg>
                                        </span>
                                    @else
                                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                        </span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-slate-800">{{ $material->title }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ $material->isVideo() ? 'Video' : ($material->isExternalLink() ? 'Drive / Canva' : 'Dokumen') }}</p>
                                    </div>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>

            {{-- Content: material viewer --}}
            <div id="material-viewer">
                @foreach ($materials as $index => $material)
                    <div class="material-panel {{ $index !== 0 ? 'hidden' : '' }} rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" data-panel-id="{{ $index }}">
                        <div class="flex flex-wrap items-start gap-3">
                            @if ($material->isVideo())
                                <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">Video</span>
                            @elseif ($material->isExternalLink())
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Google Drive / Canva</span>
                            @else
                                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">Dokumen</span>
                            @endif
                        </div>
                        <h2 class="mt-3 text-xl font-bold text-slate-950">{{ $material->title }}</h2>
                        @if ($material->description)
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $material->description }}</p>
                        @endif

                        @if ($material->isVideo())
                            @if ($material->videoEmbedUrl())
                                <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-black shadow-sm" aria-label="Pemutar video {{ $material->title }}">
                                    <div class="aspect-video">
                                        <iframe
                                            src="{{ $material->videoEmbedUrl() }}"
                                            title="{{ $material->title }}"
                                            class="h-full w-full"
                                            loading="lazy"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                </div>
                            @else
                                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-sm font-semibold text-amber-800">Tautan video tidak dapat diputar.</p>
                                    <p class="mt-1 text-sm text-amber-700">Hubungi pengajar untuk memperbarui tautan video.</p>
                                </div>
                            @endif
                        @elseif ($material->isExternalLink())
                            @if ($material->canvaDesignId())
                                <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-slate-50" aria-label="Pratinjau Canva {{ $material->title }}">
                                    <div class="canva-embed" data-design-id="{{ $material->canvaDesignId() }}" data-height-ratio="0.5625" style="padding: 56.25% 0 0; position: relative; width: 100%;"></div>
                                </div>
                                <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Buka di Canva</a>
                                <p class="mt-2 text-xs leading-5 text-slate-500">Jika pratinjau tidak muncul, pastikan desain sudah diaktifkan melalui menu Bagikan → Sematkan (Embed) di Canva.</p>
                            @elseif ($material->externalEmbedUrl())
                                <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-slate-50" aria-label="Pratinjau {{ $material->title }}">
                                    <div class="aspect-video">
                                        <iframe
                                            src="{{ $material->externalEmbedUrl() }}"
                                            title="{{ $material->title }}"
                                            class="h-full w-full"
                                            loading="lazy"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allow="fullscreen"
                                            allowfullscreen
                                        ></iframe>
                                    </div>
                                </div>
                                <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex items-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Buka di tab baru</a>
                                <p class="mt-2 text-xs leading-5 text-slate-500">Jika pratinjau tidak muncul, pastikan pemilik materi mengizinkan akses melalui tautan.</p>
                            @else
                                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-sm font-semibold text-amber-900">Tautan ini tidak bisa ditampilkan sebagai pratinjau.</p>
                                    <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex rounded-lg bg-amber-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-900">Buka materi</a>
                                </div>
                            @endif
                        @else
                            @if ($material->isPdf())
                                <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-slate-100" aria-label="Pratinjau PDF {{ $material->title }}">
                                    <iframe src="{{ route('subject-materials.preview', [$subject, $material]) }}" title="{{ $material->title }}" class="h-[70vh] min-h-[520px] w-full" loading="lazy"></iframe>
                                </div>
                                <a href="{{ route('subject-materials.download', [$subject, $material]) }}" class="mt-3 inline-flex items-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Unduh PDF</a>
                            @else
                            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-5">
                                <div class="flex items-center gap-4">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $material->original_file_name }}</p>
                                        <p class="mt-0.5 text-xs text-slate-500">{{ number_format($material->file_size / 1024, 0) }} KB · Diunggah {{ $material->created_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('subject-materials.download', [$subject, $material]) }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    Unduh materi
                                </a>
                            </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        @if ($materials->contains(fn ($material) => $material->canvaDesignId() !== null))
            <script async src="https://sdk.canva.com/v1/embed.js"></script>
        @endif

        <script>
            (function () {
                // Tab switching
                document.querySelectorAll('.material-tab').forEach(function (tab) {
                    tab.addEventListener('click', function () {
                        const id = this.dataset.materialId;
                        document.querySelectorAll('.material-tab').forEach(function (t) {
                            t.classList.remove('bg-indigo-50');
                            t.setAttribute('aria-selected', 'false');
                        });
                        this.classList.add('bg-indigo-50');
                        this.setAttribute('aria-selected', 'true');
                        document.querySelectorAll('.material-panel').forEach(function (p) {
                            p.classList.add('hidden');
                        });
                        document.querySelector('[data-panel-id="' + id + '"]').classList.remove('hidden');
                    });
                });

                // Filter tabs
                const filterBtns = document.querySelectorAll('[data-filter]');
                filterBtns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const filter = this.dataset.filter;
                        filterBtns.forEach(function (b) {
                            b.classList.remove('bg-indigo-700', 'text-white');
                            b.classList.add('border-slate-200', 'bg-white', 'text-slate-600');
                        });
                        this.classList.add('bg-indigo-700', 'text-white');
                        this.classList.remove('border-slate-200', 'bg-white', 'text-slate-600');

                        document.querySelectorAll('#material-list li').forEach(function (li) {
                            if (filter === 'all' || li.dataset.materialType === filter) {
                                li.classList.remove('hidden');
                            } else {
                                li.classList.add('hidden');
                            }
                        });

                        const visibleTabs = [...document.querySelectorAll('#material-list li:not(.hidden) .material-tab')];
                        const selectedTab = document.querySelector('.material-tab[aria-selected="true"]');
                        if (visibleTabs.length > 0 && !visibleTabs.includes(selectedTab)) {
                            visibleTabs[0].click();
                        }
                    });
                });
            })();
        </script>
    @endif
@endsection
