@extends('layouts.portal')

@section('title', $package->title.' | Bank Soal')

@section('content')
<div class="space-y-6">
    <header>
        <a href="{{ route($questionBankRoutePrefix) }}" class="text-sm font-semibold text-indigo-700 hover:underline">← Kembali ke Bank Soal</a>
        <div class="mt-5 flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">{{ $package->subject->name }}</p>
                @if (auth()->user()->role === \App\Models\User::ROLE_ADMIN_REMOTE)<p class="mt-1 text-sm text-slate-600">{{ $package->subject->school->name ?? 'Sekolah' }} · {{ $package->subject->classroom->name ?? 'Rombel' }}</p>@endif
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $package->title }}</h1>
                <p class="mt-2 text-base text-slate-600">{{ $questions->count() }} soal dalam paket ini</p>
            </div>
            <div class="flex gap-2">
                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">{{ $questions->where('type', \App\Models\ExamQuestion::TYPE_MULTIPLE_CHOICE)->count() }} PG</span>
                <span class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">{{ $questions->where('type', \App\Models\ExamQuestion::TYPE_ESSAY)->count() }} esai</span>
            </div>
        </div>
    </header>

    @if ($questions->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <h2 class="text-xl font-semibold text-slate-950">Paket ini belum memiliki soal</h2>
            <a href="{{ route($questionBankRoutePrefix) }}" class="mt-4 inline-flex rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Kembali ke Bank Soal</a>
        </div>
    @else
        <section class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4" aria-label="Filter jenis soal">
            <p class="text-sm font-medium text-slate-700">Tampilkan jenis soal</p>
            <div class="flex gap-2">
                <button type="button" data-type-filter="all" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Semua</button>
                <button type="button" data-type-filter="multiple_choice" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">Pilihan ganda</button>
                <button type="button" data-type-filter="essay" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">Esai</button>
            </div>
        </section>
        @if ($isPackageLocked)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-950" role="status">Paket ini sedang atau pernah digunakan sesi ujian. Soal dikunci agar isi ujian tidak berubah.</div>
        @else
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="openPackageModal('add-question-modal')" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">+ Tambah soal</button>
                <button type="button" onclick="openPackageModal('import-question-modal')" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Impor soal ke paket</button>
            </div>
        @endif
        <section class="grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_220px]" data-question-browser>
            <div class="flex flex-col rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                @foreach ($questions as $index => $question)
                    <article data-question-item="{{ $loop->index }}" data-question-type="{{ $question->type }}" @if (! $loop->first) hidden @endif class="order-2">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-indigo-50 px-3 py-1.5 text-sm font-bold text-indigo-800">Soal {{ $index + 1 }}</span>
                                <span @class(['rounded-md px-3 py-1.5 text-sm font-semibold', 'bg-violet-50 text-violet-800' => $question->isEssay(), 'bg-slate-100 text-slate-700' => ! $question->isEssay()])>{{ $question->isEssay() ? 'Esai / uraian' : 'Pilihan ganda' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-slate-600">{{ $question->points }} poin</span>
                                @if (! $isPackageLocked)
                                    <button type="button" data-edit-toggle="{{ $loop->index }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Edit</button>
                                @endif
                                @if (! $isPackageLocked)
                                    <form method="POST" action="{{ route($questionBankRoutePrefix.'.destroy', $question) }}" data-confirm="Soal ini akan dihapus dari paket. Tindakan ini tidak bisa dibatalkan." data-confirm-title="Hapus soal?" data-confirm-btn="Ya, hapus soal" data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-rose-200 px-3 py-1.5 text-sm font-semibold text-rose-700 hover:bg-rose-50">Hapus soal</button>
                                    </form>
                                @else
                                    <span class="rounded-md bg-slate-50 px-2 py-1 text-xs text-slate-500">Soal terkunci</span>
                                @endif
                            </div>
                        </div>
                        @if (! $isPackageLocked)
                            <form method="POST" action="{{ route($questionBankRoutePrefix.'.package.questions.update', [$package, $question]) }}" data-question-edit-form="{{ $loop->index }}" hidden class="mt-5 space-y-4 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4">
                                @csrf
                                @method('PUT')
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div><label class="mb-1 block text-sm font-medium" for="edit-type-{{ $question->id }}">Jenis soal</label><select id="edit-type-{{ $question->id }}" name="type" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm" data-question-type-select><option value="multiple_choice" @selected($question->isMultipleChoice())>Pilihan ganda</option><option value="essay" @selected($question->isEssay())>Esai</option></select></div>
                                    <div><label class="mb-1 block text-sm font-medium" for="edit-points-{{ $question->id }}">Poin</label><input id="edit-points-{{ $question->id }}" name="points" type="number" min="1" max="100" value="{{ $question->points }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></div>
                                </div>
                                <div><label class="mb-1 block text-sm font-medium" for="edit-prompt-{{ $question->id }}">Pertanyaan</label><textarea id="edit-prompt-{{ $question->id }}" name="prompt" rows="3" required maxlength="5000" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">{{ $question->prompt }}</textarea></div>
                                <div data-multiple-choice-fields @if ($question->isEssay()) hidden @endif class="grid gap-3 sm:grid-cols-2">
                                    @foreach (['A', 'B', 'C', 'D'] as $key)
                                        <div><label class="mb-1 block text-sm font-medium" for="edit-option-{{ $question->id }}-{{ $key }}">Pilihan {{ $key }}</label><input id="edit-option-{{ $question->id }}-{{ $key }}" name="options[{{ $key }}]" value="{{ $question->options[$key] ?? '' }}" maxlength="1000" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></div>
                                    @endforeach
                                    <div><label class="mb-1 block text-sm font-medium" for="edit-answer-{{ $question->id }}">Kunci jawaban</label><select id="edit-answer-{{ $question->id }}" name="answer_key" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm">@foreach (['A', 'B', 'C', 'D'] as $key)<option value="{{ $key }}" @selected($question->answer_key === $key)>{{ $key }}</option>@endforeach</select></div>
                                </div>
                                <div data-essay-fields @if ($question->isMultipleChoice()) hidden @endif><label class="mb-1 block text-sm font-medium" for="edit-sample-{{ $question->id }}">Pedoman jawaban</label><textarea id="edit-sample-{{ $question->id }}" name="sample_answer" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm">{{ $question->sample_answer }}</textarea></div>
                                <div class="flex justify-end gap-2"><button type="button" data-edit-cancel="{{ $loop->index }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold">Batal</button><button type="submit" class="rounded-lg bg-indigo-700 px-3 py-2 text-sm font-semibold text-white">Simpan perubahan</button></div>
                            </form>
                        @endif
                        <h2 class="mt-5 text-xl font-semibold leading-8 text-slate-950">{{ $question->prompt }}</h2>
                        @if ($question->isEssay())
                            <div class="mt-6 rounded-lg border border-violet-100 bg-violet-50 p-4"><p class="text-sm font-semibold text-violet-900">Pedoman jawaban</p><p class="mt-2 text-base leading-7 text-slate-700">{{ $question->sample_answer ?: 'Belum ada pedoman jawaban.' }}</p></div>
                        @else
                            @php($options = $question->options ?? [])
                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                @foreach (['A', 'B', 'C', 'D'] as $key)
                                    @if (isset($options[$key]))
                                        @php($isCorrect = strtoupper((string) $question->answer_key) === $key)
                                        <div @class(['flex items-start gap-3 rounded-lg border p-4 text-base leading-6', 'border-emerald-200 bg-emerald-50 text-emerald-950' => $isCorrect, 'border-slate-200 bg-slate-50 text-slate-700' => ! $isCorrect])><span class="font-bold">{{ $key }}.</span><span>{{ $options[$key] }}</span>@if ($isCorrect)<span class="ml-auto text-sm font-semibold text-emerald-700">Kunci</span>@endif</div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
                @if (! $isPackageLocked)
                    <div id="add-question-modal" class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto overscroll-contain bg-slate-950/50 p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="add-question-title">
                      <div class="my-auto max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:max-h-[calc(100dvh-3rem)] sm:p-6">
                        <div class="flex items-start justify-between border-b border-slate-100 pb-4"><div><h2 id="add-question-title" class="text-xl font-semibold text-slate-950">Tambah soal ke paket</h2><p class="mt-1 text-sm text-slate-600">Soal akan langsung ditambahkan ke “{{ $package->title }}”.</p></div><button type="button" onclick="closePackageModal('add-question-modal')" class="rounded-lg px-3 py-1 text-xl text-slate-500 hover:bg-slate-100" aria-label="Tutup">&times;</button></div>
                        <form method="POST" action="{{ route($questionBankRoutePrefix.'.package.questions.store', $package) }}" class="space-y-4 border-t border-indigo-100 p-4">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div><label class="mb-1 block text-sm font-medium" for="new-type">Jenis soal</label><select id="new-type" name="type" data-question-type-select class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"><option value="multiple_choice">Pilihan ganda</option><option value="essay">Esai</option></select></div>
                                <div><label class="mb-1 block text-sm font-medium" for="new-points">Poin</label><input id="new-points" name="points" type="number" min="1" max="100" value="10" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></div>
                            </div>
                            <div><label class="mb-1 block text-sm font-medium" for="new-prompt">Pertanyaan</label><textarea id="new-prompt" name="prompt" rows="3" required maxlength="5000" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></textarea></div>
                            <div data-multiple-choice-fields class="grid gap-3 sm:grid-cols-2">
                                @foreach (['A', 'B', 'C', 'D'] as $key)
                                    <div><label class="mb-1 block text-sm font-medium" for="new-option-{{ $key }}">Pilihan {{ $key }}</label><input id="new-option-{{ $key }}" name="options[{{ $key }}]" maxlength="1000" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></div>
                                @endforeach
                                <div><label class="mb-1 block text-sm font-medium" for="new-answer">Kunci jawaban</label><select id="new-answer" name="answer_key" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm"><option>A</option><option>B</option><option>C</option><option>D</option></select></div>
                            </div>
                            <div data-essay-fields hidden><label class="mb-1 block text-sm font-medium" for="new-sample">Pedoman jawaban</label><textarea id="new-sample" name="sample_answer" rows="3" maxlength="2000" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm"></textarea></div>
                            <div class="flex justify-end"><button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white">Simpan ke paket ini</button></div>
                        </form>
                      </div>
                    </div>

                    <div id="import-question-modal" class="fixed inset-0 z-[100] hidden items-center justify-center overflow-y-auto overscroll-contain bg-slate-950/50 p-3 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="import-question-title">
                      <div class="my-auto max-h-[calc(100dvh-1.5rem)] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:max-h-[calc(100dvh-3rem)] sm:p-6">
                        <div class="flex items-start justify-between border-b border-slate-100 pb-4"><div><h2 id="import-question-title" class="text-xl font-semibold text-slate-950">Impor soal ke paket</h2><p class="mt-1 text-sm text-slate-600">File akan ditambahkan ke “{{ $package->title }}”.</p></div><button type="button" onclick="closePackageModal('import-question-modal')" class="rounded-lg px-3 py-1 text-xl text-slate-500 hover:bg-slate-100" aria-label="Tutup">&times;</button></div>
                        <form method="POST" action="{{ route($questionBankRoutePrefix.'.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ $package->id }}">
                            <input type="hidden" name="subject_id" value="{{ $package->subject_id }}">
                            <div class="flex flex-wrap gap-4"><a href="{{ route($questionBankRoutePrefix.'.template.combined') }}" class="inline-flex text-sm font-semibold text-indigo-700 underline underline-offset-4">Template gabungan PG + esai (CSV)</a><a href="{{ route($questionBankRoutePrefix.'.template.pdf') }}" class="inline-flex text-sm font-semibold text-rose-700 underline underline-offset-4">Contoh PDF impor</a></div>
                            <div><label for="package-import-file" class="mb-1.5 block text-sm font-medium text-slate-800">File (.xlsx, .csv, .txt, .pdf)</label><input id="package-import-file" type="file" name="file" accept=".xlsx,.csv,.txt,.pdf" required class="w-full rounded-lg border border-slate-300 p-2 text-sm"><p class="mt-1 text-xs leading-5 text-slate-500">PDF harus berisi teks yang bisa diseleksi, bukan hasil scan/foto.</p></div>
                            @error('file')<p class="text-sm text-rose-700" role="alert">{{ $message }}</p>@enderror
                            <div class="flex justify-end gap-2 border-t border-slate-100 pt-4"><button type="button" onclick="closePackageModal('import-question-modal')" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700">Batal</button><button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white">Impor ke paket</button></div>
                        </form>
                      </div>
                    </div>
                @endif
            </div>
            <aside class="rounded-xl border border-slate-200 bg-white p-4 lg:sticky lg:top-5">
                <h2 class="text-sm font-semibold text-slate-900">Pilih nomor soal</h2>
                <nav class="mt-4 grid max-h-96 grid-cols-4 content-start gap-2 overflow-y-auto" aria-label="Nomor soal">
                    @foreach ($questions as $question)
                        <button type="button" data-question-number="{{ $loop->index }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="flex h-9 items-center justify-center rounded-md border text-sm font-semibold transition {{ $loop->first ? 'border-indigo-700 bg-indigo-700 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-indigo-50' }}">{{ $loop->iteration }}</button>
                    @endforeach
                </nav>
            </aside>
        </section>
    @endif
</div>

<script>
    const packageModalIds = ['add-question-modal', 'import-question-modal'];

    packageModalIds.forEach((id) => {
        const modal = document.getElementById(id);
        if (modal) {
            document.body.append(modal);
        }
    });

    function openPackageModal(id) {
        const modal = document.getElementById(id);
        if (!modal) {
            return;
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }

    function closePackageModal(id) {
        const modal = document.getElementById(id);
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.classList.remove('flex');

        if (packageModalIds.every((modalId) => document.getElementById(modalId)?.classList.contains('hidden'))) {
            document.body.classList.remove('overflow-hidden');
        }
    }

    document.querySelectorAll('[data-question-browser]').forEach((browser) => {
        const cards = [...browser.querySelectorAll('[data-question-item]')];
        const buttons = [...browser.querySelectorAll('[data-question-number]')];
        const filters = [...document.querySelectorAll('[data-type-filter]')];
        let activeFilter = 'all';
        let activeQuestion = 0;

        const renderQuestions = () => {
            const matchingCards = cards.filter((card) => activeFilter === 'all' || card.dataset.questionType === activeFilter);
            cards.forEach((card) => card.hidden = !matchingCards.includes(card) || matchingCards.indexOf(card) !== activeQuestion);
            buttons.forEach((button) => {
                const card = cards.find((item) => item.dataset.questionItem === button.dataset.questionNumber);
                button.hidden = !card || (activeFilter !== 'all' && card.dataset.questionType !== activeFilter);
                const active = card === matchingCards[activeQuestion];
                button.setAttribute('aria-pressed', String(active));
                button.classList.toggle('border-indigo-700', active);
                button.classList.toggle('bg-indigo-700', active);
                button.classList.toggle('text-white', active);
                button.classList.toggle('border-slate-200', !active);
                button.classList.toggle('bg-white', !active);
                button.classList.toggle('text-slate-700', !active);
            });
        };

        buttons.forEach((button) => button.addEventListener('click', () => {
            const selectedCard = cards.findIndex((card) => card.dataset.questionItem === button.dataset.questionNumber);
            activeQuestion = cards.slice(0, selectedCard + 1).filter((card) => activeFilter === 'all' || card.dataset.questionType === activeFilter).length - 1;
            renderQuestions();
        }));

        filters.forEach((filter) => filter.addEventListener('click', () => {
            activeFilter = filter.dataset.typeFilter;
            activeQuestion = 0;
            filters.forEach((item) => {
                item.classList.toggle('bg-slate-900', item === filter);
                item.classList.toggle('text-white', item === filter);
                item.classList.toggle('bg-slate-100', item !== filter);
                item.classList.toggle('text-slate-700', item !== filter);
            });
            renderQuestions();
        }));
    });

    document.querySelectorAll('[data-edit-toggle]').forEach((button) => button.addEventListener('click', () => {
        const form = document.querySelector(`[data-question-edit-form="${button.dataset.editToggle}"]`);
        if (form) form.hidden = !form.hidden;
    }));
    document.querySelectorAll('[data-edit-cancel]').forEach((button) => button.addEventListener('click', () => {
        const form = document.querySelector(`[data-question-edit-form="${button.dataset.editCancel}"]`);
        if (form) form.hidden = true;
    }));
    document.querySelectorAll('[data-question-type-select]').forEach((select) => select.addEventListener('change', () => {
        const form = select.closest('form');
        const isEssay = select.value === 'essay';
        form.querySelectorAll('[data-multiple-choice-fields]').forEach((section) => section.hidden = isEssay);
        form.querySelectorAll('[data-essay-fields]').forEach((section) => section.hidden = !isEssay);
    }));
</script>
@endsection
