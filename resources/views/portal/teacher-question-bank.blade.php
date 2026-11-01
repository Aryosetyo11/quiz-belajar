@extends('layouts.portal')

@section('title', 'Bank Soal | RuangUji')

@section('content')
<div class="space-y-6">
    <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">{{ $isGlobalQuestionBankManager ? 'Pusat Pengelolaan' : 'Panel Pengajar' }}</span>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Bank Soal</h1>
            <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">{{ $isGlobalQuestionBankManager ? 'Tinjau dan kelola paket soal dari seluruh mata pelajaran.' : 'Kelola paket dan tinjau soal sebelum digunakan dalam sesi ujian.' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openImportModal()" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Impor file</button>
            <a href="{{ route($questionBankRoutePrefix.'.create') }}" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Buat soal manual</a>
        </div>
    </header>


    <section class="grid gap-3 sm:grid-cols-3" aria-label="Ringkasan soal">
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-sm text-slate-600">Semua soal</p><p class="mt-1 text-2xl font-semibold text-slate-950">{{ $totalQuestionsCount }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-sm text-slate-600">Pilihan ganda</p><p class="mt-1 text-2xl font-semibold text-slate-950">{{ $multipleChoiceCount }}</p></div>
        <div class="rounded-xl border border-slate-200 bg-white p-4"><p class="text-sm text-slate-600">Esai / uraian</p><p class="mt-1 text-2xl font-semibold text-slate-950">{{ $essayCount }}</p></div>
    </section>

    <section class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5" aria-label="Filter soal">
        <div class="flex flex-wrap gap-2 border-b border-slate-100 pb-4">
            <a href="{{ request()->fullUrlWithQuery(['type' => 'all']) }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold', 'bg-slate-900 text-white' => $selectedType === 'all', 'bg-slate-100 text-slate-700 hover:bg-slate-200' => $selectedType !== 'all'])>Semua ({{ $totalQuestionsCount }})</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'multiple_choice']) }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold', 'bg-indigo-700 text-white' => $selectedType === 'multiple_choice', 'bg-slate-100 text-slate-700 hover:bg-slate-200' => $selectedType !== 'multiple_choice'])>Pilihan ganda ({{ $multipleChoiceCount }})</a>
            <a href="{{ request()->fullUrlWithQuery(['type' => 'essay']) }}" @class(['rounded-lg px-3 py-2 text-sm font-semibold', 'bg-violet-700 text-white' => $selectedType === 'essay', 'bg-slate-100 text-slate-700 hover:bg-slate-200' => $selectedType !== 'essay'])>Esai ({{ $essayCount }})</a>
        </div>
        <form method="GET" action="{{ route($questionBankRoutePrefix) }}" class="mt-4 grid gap-3 md:grid-cols-[minmax(0,1fr)_240px_auto]">
            <input type="hidden" name="type" value="{{ $selectedType }}">
            <label class="sr-only" for="question-search">Cari soal</label>
            <input id="question-search" name="search" type="search" value="{{ $search }}" placeholder="Cari soal..." class="rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm outline-none focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">
            <label class="sr-only" for="subject-filter">Pilih mata pelajaran</label>
            <select id="subject-filter" name="subject_id" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm outline-none focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">
                <option value="">Semua mata pelajaran</option>
                @foreach ($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected((string) $selectedSubjectId === (string) $subject->id)>{{ $subject->name }}@if ($isGlobalQuestionBankManager) — {{ $subject->school->name ?? 'Sekolah' }} · {{ $subject->classroom->name ?? 'Rombel' }}@endif</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Cari</button>
                @if ($search !== '' || $selectedSubjectId || $selectedType !== 'all')
                    <a href="{{ route($questionBankRoutePrefix) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                @endif
            </div>
        </form>
    </section>

    @if ($questions->isNotEmpty())
        <div class="space-y-4">
        @foreach ($questions->groupBy('exam_id') as $packageQuestions)
        @php($package = $packageQuestions->first()->exam)
        @php($packageMultipleChoiceCount = $packageQuestions->where('type', \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE)->count())
        @php($packageEssayCount = $packageQuestions->where('type', \App\Models\ExamQuestion::TYPE_ESSAY)->count())
        <a href="{{ route($questionBankRoutePrefix.'.package', $package) }}" class="group block rounded-xl border border-slate-200 bg-white px-5 py-4 transition hover:border-indigo-300 hover:shadow-sm focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-100 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $package->subject->name }}@if ($isGlobalQuestionBankManager) · {{ $package->subject->school->name ?? 'Sekolah' }} · {{ $package->subject->classroom->name ?? 'Rombel' }}@endif</p>
                    <h2 class="mt-1 text-lg font-semibold text-slate-950">{{ $package->title }}</h2>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex flex-wrap gap-1.5">
                        @if ($packageMultipleChoiceCount > 0)
                            <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">{{ $packageMultipleChoiceCount }} PG</span>
                        @endif
                        @if ($packageEssayCount > 0)
                            <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">{{ $packageEssayCount }} esai</span>
                        @endif
                        <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">{{ $packageQuestions->count() }} soal</span>
                    </div>
                    <span class="text-sm font-semibold text-indigo-700 group-hover:text-indigo-900">Buka paket <span aria-hidden="true">→</span></span>
                </div>
            </div>
        </a>
        @endforeach
        </div>
    @else
        <section class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <h2 class="text-xl font-semibold text-slate-950">{{ $search !== '' || $selectedSubjectId || $selectedType !== 'all' ? 'Soal tidak ditemukan' : 'Belum ada soal' }}</h2>
            <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">{{ $search !== '' || $selectedSubjectId || $selectedType !== 'all' ? 'Coba ubah pencarian atau reset filter.' : 'Buat paket soal manual atau impor file untuk mulai mengisi bank soal.' }}</p>
            <div class="mt-5 flex justify-center gap-3"><button type="button" onclick="openImportModal()" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Impor file</button><a href="{{ route($questionBankRoutePrefix.'.create') }}" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white">Buat soal manual</a></div>
        </section>
    @endif

    <details class="rounded-xl border border-slate-200 bg-white">
        <summary class="cursor-pointer px-5 py-4 text-sm font-semibold text-slate-800">Unduh template impor</summary>
        <div class="flex flex-wrap gap-3 border-t border-slate-100 p-5"><a href="{{ route($questionBankRoutePrefix.'.template.combined') }}" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white">Template gabungan PG + esai</a><a href="{{ route($questionBankRoutePrefix.'.template.pg') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Template pilihan ganda</a><a href="{{ route($questionBankRoutePrefix.'.template.essay') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Template esai</a><a href="{{ route($questionBankRoutePrefix.'.template.pdf') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Contoh dokumen</a></div>
    </details>
</div>

<div id="import-modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="import-title">
    <div class="mx-auto mt-12 w-full max-w-lg rounded-xl bg-white p-5 shadow-xl sm:p-6">
        <div class="flex items-start justify-between"><div><h2 id="import-title" class="text-xl font-semibold text-slate-950">Impor soal</h2><p class="mt-1 text-sm text-slate-600">Impor akan membuat paket baru.</p></div><button type="button" onclick="closeImportModal()" class="rounded-lg px-3 py-1 text-xl text-slate-500 hover:bg-slate-100" aria-label="Tutup">&times;</button></div>
        <form method="POST" action="{{ route($questionBankRoutePrefix.'.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
            @csrf
            <div><label for="import-subject-id" class="mb-1.5 block text-sm font-medium text-slate-800">Mata pelajaran</label><select id="import-subject-id" name="subject_id" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"><option value="">Pilih mata pelajaran</option>@foreach ($subjects as $subject)<option value="{{ $subject->id }}">{{ $subject->name }}@if ($isGlobalQuestionBankManager) — {{ $subject->school->name ?? 'Sekolah' }} · {{ $subject->classroom->name ?? 'Rombel' }}@endif</option>@endforeach</select></div>
            <div><label for="import-title" class="mb-1.5 block text-sm font-medium text-slate-800">Nama paket</label><input id="import-title" name="title" required maxlength="160" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm" placeholder="Contoh: Latihan Bab 1"></div>
            <div><label for="import-file" class="mb-1.5 block text-sm font-medium text-slate-800">File (.xlsx, .csv, .txt, .pdf)</label><input id="import-file" type="file" name="file" accept=".xlsx,.xls,.csv,.txt,.pdf" required class="w-full rounded-lg border border-slate-300 p-2 text-sm"></div>
            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" onclick="closeImportModal()" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Batal</button><button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white">Impor dan simpan</button></div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('[data-question-browser]').forEach((browser) => {
        const cards = [...browser.querySelectorAll('[data-question-item]')];
        const buttons = [...browser.querySelectorAll('[data-question-number]')];
        buttons.forEach((button) => button.addEventListener('click', () => {
            const selected = button.dataset.questionNumber;
            cards.forEach((card) => card.hidden = card.dataset.questionItem !== selected);
            buttons.forEach((item) => {
                const active = item === button;
                item.setAttribute('aria-pressed', String(active));
                item.classList.toggle('border-indigo-700', active);
                item.classList.toggle('bg-indigo-700', active);
                item.classList.toggle('text-white', active);
                item.classList.toggle('border-slate-200', ! active);
                item.classList.toggle('bg-white', ! active);
                item.classList.toggle('text-slate-700', ! active);
            });
        }));
    });
    function openImportModal() { document.getElementById('import-modal')?.classList.remove('hidden'); }
    function closeImportModal() { document.getElementById('import-modal')?.classList.add('hidden'); }
</script>
@endsection
