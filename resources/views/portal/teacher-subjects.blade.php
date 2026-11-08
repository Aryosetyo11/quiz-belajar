@extends('layouts.portal')

@section('title', 'Mata Pelajaran | RuangUji')

@section('content')
<div class="space-y-7">
    <header class="flex flex-col gap-5 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold text-indigo-700">Ruang mengajar</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Mata pelajaran</h1>
            <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Pilih kelas untuk mengelola siswa, materi, dan ujian.</p>
        </div>
        <button type="button" onclick="openCreateModal()" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>
            Buat kelas
        </button>
    </header>

    <section class="grid gap-3 sm:grid-cols-3" aria-label="Ringkasan kelas">
        <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
            <p class="text-sm font-medium text-slate-600">Kelas diampu</p>
            <p class="mt-1 text-3xl font-semibold tabular-nums text-slate-950">{{ $subjects->count() }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
            <p class="text-sm font-medium text-slate-600">Total siswa terdaftar</p>
            <p class="mt-1 text-3xl font-semibold tabular-nums text-slate-950">{{ $subjects->sum('students_count') }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5">
            <p class="text-sm font-medium text-slate-600">Materi dibagikan</p>
            <p class="mt-1 text-3xl font-semibold tabular-nums text-slate-950">{{ $subjects->sum('materials_count') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $subjects->sum('documents_count') }} dokumen · {{ $subjects->sum('videos_count') }} video · {{ $subjects->sum('links_count') }} tautan</p>
        </div>
    </section>

    @if ($subjects->isNotEmpty())
        <section aria-label="Daftar mata pelajaran">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">Kelas yang saya ampu</h2>
                    <p class="mt-1 text-sm text-slate-600">Cari berdasarkan mata pelajaran atau nama rombel.</p>
                </div>
                <div class="flex w-full items-center gap-3 sm:w-auto">
                    <label class="relative block min-w-0 flex-1 sm:w-80">
                        <span class="sr-only">Cari mata pelajaran atau rombel</span>
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-4-4"/></svg>
                        <input id="filter-subjects-input" type="search" placeholder="Cari mata pelajaran atau rombel" class="block min-h-11 w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-3.5 text-sm text-slate-900 placeholder:text-slate-500 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">
                    </label>
                    <span id="subject-count-label" class="shrink-0 text-sm font-medium text-slate-600" aria-live="polite">{{ $subjects->count() }} kelas</span>
                </div>
            </div>

            <div id="subject-grid" class="grid gap-4 md:grid-cols-2 2xl:grid-cols-3">
                @foreach ($subjects as $subject)
                    @php
                        $searchTerms = trim($subject->name.' '.($subject->classroom?->name ?? '').' '.($subject->classroom?->academic_year ?? '').' '.($subject->description ?? ''));
                    @endphp
                    <article data-subject-card data-search="{{ mb_strtolower($searchTerms) }}" class="flex min-h-full flex-col rounded-xl border border-slate-200 bg-white p-5 transition-colors hover:border-indigo-300 hover:shadow-sm">
                        <div class="flex items-start gap-3.5">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-lg font-semibold text-indigo-800" aria-hidden="true">{{ mb_strtoupper(mb_substr($subject->name, 0, 1)) }}</span>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate text-xl font-semibold text-slate-950">{{ $subject->name }}</h3>
                                <p class="mt-1 text-sm font-medium text-slate-700">{{ $subject->classroom?->name ?? 'Rombel belum ditentukan' }}</p>
                                @if ($subject->classroom?->academic_year)
                                    <p class="mt-0.5 text-sm text-slate-500">Tahun ajaran {{ $subject->classroom->academic_year }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($subject->description)
                            <p class="mt-4 line-clamp-2 text-sm leading-6 text-slate-600">{{ $subject->description }}</p>
                        @else
                            <p class="mt-4 text-sm leading-6 text-slate-500">Belum ada keterangan untuk mata pelajaran ini.</p>
                        @endif

                        <dl class="mt-5 grid grid-cols-3 divide-x divide-slate-200 rounded-lg border border-slate-200 bg-slate-50 py-3 text-center">
                            <div class="px-2">
                                <dt class="text-sm text-slate-600">Siswa</dt>
                                <dd class="mt-0.5 text-lg font-semibold tabular-nums text-slate-950">{{ $subject->students_count }}</dd>
                            </div>
                            <div class="px-2">
                                <dt class="text-sm text-slate-600">Materi</dt>
                                <dd class="mt-0.5 text-lg font-semibold tabular-nums text-slate-950">{{ $subject->materials_count }}</dd>
                            </div>
                            <div class="px-2">
                                <dt class="text-sm text-slate-600">Sesi ujian</dt>
                                <dd class="mt-0.5 text-lg font-semibold tabular-nums text-slate-950">{{ $subject->exams_count }}</dd>
                            </div>
                        </dl>

                        <div class="mt-auto pt-5">
                            <a href="{{ route('teacher.subjects.show', $subject) }}" class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-800 transition hover:border-indigo-700 hover:bg-indigo-700 hover:text-white focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">
                                Buka kelas
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div id="subject-search-empty" hidden class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white px-6 py-10 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Kelas tidak ditemukan</h3>
                <p class="mt-1 text-sm text-slate-600">Coba kata kunci mata pelajaran atau rombel yang lain.</p>
            </div>
        </section>
    @else
        <section class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center sm:px-10">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75A8.25 8.25 0 0 0 4.5 4v13.5A8.25 8.25 0 0 1 12 20.25m0-13.5A8.25 8.25 0 0 1 19.5 4v13.5A8.25 8.25 0 0 0 12 20.25m0-13.5v13.5"/></svg>
            </div>
            <h2 class="mt-4 text-xl font-semibold text-slate-950">Belum ada kelas yang diampu</h2>
            <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Buat kelas mata pelajaran untuk mengatur siswa dan membagikan materi. Pilih rombel sekolah agar siswanya bisa terdaftar di kelas.</p>
            <button type="button" onclick="openCreateModal()" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200">Buat kelas pertama</button>
        </section>
    @endif
</div>

<div id="create-class-modal" data-skip-reveal class="fixed inset-0 z-[60] hidden items-center justify-center overflow-y-auto overscroll-contain bg-slate-950/50 p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="create-class-title">
    <div class="my-auto w-full max-w-xl rounded-xl bg-white p-5 shadow-xl sm:p-6">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
                <h2 id="create-class-title" class="text-xl font-semibold text-slate-950">Buat kelas mata pelajaran</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Siswa dari rombel yang dipilih akan otomatis terdaftar di kelas ini.</p>
            </div>
            <button type="button" onclick="closeCreateModal()" class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" aria-label="Tutup">&times;</button>
        </div>

        <form method="POST" action="{{ route('teacher.subjects.store') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="create-classroom" class="mb-1.5 block text-sm font-semibold text-slate-800">Rombel sekolah <span class="text-rose-600">*</span></label>
                <select id="create-classroom" name="classroom_id" required @disabled($classrooms->isEmpty()) class="min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-base text-slate-900 outline-none focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 disabled:cursor-not-allowed disabled:bg-slate-100">
                    <option value="">Pilih rombel</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}" @selected((string) old('classroom_id') === (string) $classroom->id)>{{ $classroom->name }} · {{ $classroom->academic_year }}</option>
                    @endforeach
                </select>
                @if ($classrooms->isEmpty())
                    <p class="mt-2 rounded-lg bg-amber-50 px-3 py-2.5 text-sm leading-5 text-amber-900">Belum ada rombel untuk sekolah ini. Hubungi admin sekolah untuk membuat rombel terlebih dahulu.</p>
                @endif
                @error('classroom_id')<p class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="create-name" class="mb-1.5 block text-sm font-semibold text-slate-800">Nama mata pelajaran <span class="text-rose-600">*</span></label>
                <input type="text" id="create-name" name="name" value="{{ old('name') }}" required maxlength="120" placeholder="Contoh: Matematika" class="min-h-11 w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-900 placeholder:text-slate-500 outline-none focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">
                @error('name')<p class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="create-description" class="mb-1.5 block text-sm font-semibold text-slate-800">Keterangan <span class="font-normal text-slate-500">(opsional)</span></label>
                <textarea id="create-description" name="description" rows="3" maxlength="1000" placeholder="Tujuan atau ringkasan singkat kelas" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-900 placeholder:text-slate-500 outline-none focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">{{ old('description') }}</textarea>
                @error('description')<p class="mt-1.5 text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-4 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeCreateModal()" class="min-h-11 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                <button type="submit" @disabled($classrooms->isEmpty()) class="min-h-11 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-200 disabled:cursor-not-allowed disabled:opacity-50">Simpan kelas</button>
            </div>
        </form>
    </div>
</div>

<script>
    const createClassModal = document.getElementById('create-class-modal');
    if (createClassModal) {
        document.body.append(createClassModal);
    }

    function openCreateModal() {
        createClassModal?.classList.remove('hidden');
        createClassModal?.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => document.getElementById('create-name')?.focus(), 50);
    }

    function closeCreateModal() {
        createClassModal?.classList.add('hidden');
        createClassModal?.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }

    createClassModal?.addEventListener('click', (event) => {
        if (event.target === createClassModal) {
            closeCreateModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && createClassModal && !createClassModal.classList.contains('hidden')) {
            closeCreateModal();
        }
    });

    const filterInput = document.getElementById('filter-subjects-input');
    const subjectCards = [...document.querySelectorAll('[data-subject-card]')];
    const searchEmptyState = document.getElementById('subject-search-empty');
    const subjectCountLabel = document.getElementById('subject-count-label');

    filterInput?.addEventListener('input', () => {
        const term = filterInput.value.trim().toLocaleLowerCase();
        let visibleCount = 0;

        subjectCards.forEach((card) => {
            const matches = card.dataset.search?.includes(term) ?? false;
            card.hidden = !matches;
            visibleCount += matches ? 1 : 0;
        });

        if (subjectCountLabel) {
            subjectCountLabel.textContent = term ? `${visibleCount} ditemukan` : `${subjectCards.length} kelas`;
        }

        if (searchEmptyState) {
            searchEmptyState.hidden = visibleCount > 0;
        }
    });
</script>
@endsection
