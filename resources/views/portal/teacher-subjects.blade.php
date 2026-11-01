@extends('layouts.portal')

@section('title', 'Mata Pelajaran & Kelas | RuangUji')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Portal Pengajar</span>
                <span class="text-sm text-slate-400">•</span>
                <span class="text-sm text-slate-500">Ruang Kelas</span>
            </div>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Mata Pelajaran & Kelas</h1>
            <p class="mt-1 text-base text-slate-500">Pilih ruang kelas untuk mengelola siswa peserta, materi ajar, dan evaluasi kuis.</p>
        </div>

        <div>
            <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Buat Kelas Baru
            </button>
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            </span>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Mata Pelajaran Diampu</span>
                <p class="text-xl font-bold text-slate-900">{{ $subjects->count() }} <span class="text-sm font-normal text-slate-400">kelas</span></p>
            </div>
        </div>

        <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
            </span>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Siswa Terdaftar</span>
                <p class="text-xl font-bold text-slate-900">{{ $subjects->sum('students_count') }} <span class="text-sm font-normal text-slate-400">partisipan</span></p>
            </div>
        </div>

        <div class="flex items-center gap-3.5 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </span>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Materi Dibagikan</span>
                <p class="text-xl font-bold text-slate-900">{{ $subjects->sum('materials_count') }} <span class="text-sm font-normal text-slate-400">materi</span></p>
                <p class="mt-0.5 text-xs text-slate-500">{{ $subjects->sum('documents_count') }} modul · {{ $subjects->sum('videos_count') }} video · {{ $subjects->sum('links_count') }} tautan</p>
            </div>
        </div>
    </div>

    {{-- Search Filter & Counter --}}
    @if ($subjects->isNotEmpty())
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative max-w-md flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="filter-subjects-input" placeholder="Cari nama mata pelajaran..." class="block w-full rounded-xl border border-slate-200/90 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 shadow-2xs outline-none transition focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
            </div>
            <span id="subject-count-label" class="text-sm font-medium text-slate-500">
                Menampilkan {{ $subjects->count() }} kelas
            </span>
        </div>
    @endif

    {{-- Class Cards Grid --}}
    @if ($subjects->isNotEmpty())
        <div id="subject-grid" class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($subjects as $subject)
                <article class="subject-card group flex flex-col justify-between rounded-2xl border border-slate-200/90 bg-white p-5 shadow-2xs transition duration-200 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-md" data-name="{{ strtolower($subject->name) }}" data-desc="{{ strtolower($subject->description ?? '') }}">
                    <div>
                        {{-- Top Card Header --}}
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white shadow-2xs group-hover:scale-105 transition-transform">
                                    {{ mb_substr($subject->name, 0, 1) }}
                                </span>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">
                                        <a href="{{ route('teacher.subjects.show', $subject) }}">
                                            {{ $subject->name }}
                                        </a>
                                    </h2>
                                    <span class="text-xs text-slate-400">Kelas Aktif</span>
                                </div>
                            </div>
                        </div>

                        {{-- Description --}}
                        <p class="mt-3.5 text-sm leading-relaxed text-slate-500 line-clamp-2">
                            {{ $subject->description ?: 'Belum ada deskripsi untuk mata pelajaran ini.' }}
                        </p>

                        {{-- Stats Chips --}}
                        <div class="mt-5 grid grid-cols-3 gap-2 border-t border-slate-100 pt-3.5 text-center">
                            <div class="rounded-lg bg-slate-50/80 p-2">
                                <span class="block text-base font-bold text-slate-900">{{ $subject->students_count }}</span>
                                <span class="block text-xs text-slate-400 font-medium">Siswa</span>
                            </div>
                            <div class="rounded-lg bg-slate-50/80 p-2">
                                <span class="block text-base font-bold text-slate-900">{{ $subject->documents_count }} <span class="font-normal text-slate-400">/</span> {{ $subject->videos_count }}</span>
                                <span class="block text-xs text-slate-500 font-medium">Modul / Video / Link</span>
                            </div>
                            <div class="rounded-lg bg-slate-50/80 p-2">
                                <span class="block text-base font-bold text-slate-900">{{ $subject->exams_count }}</span>
                                <span class="block text-xs text-slate-400 font-medium">Ujian</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer Action --}}
                    <div class="mt-5 pt-3 border-t border-slate-100">
                        <a href="{{ route('teacher.subjects.show', $subject) }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-2.5 text-sm font-semibold text-white shadow-2xs transition hover:bg-indigo-600">
                            Masuk ke Kelas
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-2xs">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
            </div>
            <h2 class="mt-4 text-lg font-bold text-slate-900">Belum Ada Kelas Mata Pelajaran</h2>
            <p class="mx-auto mt-1.5 max-w-sm text-sm leading-relaxed text-slate-500">Anda belum membuat mata pelajaran. Buat kelas pertama Anda sekarang untuk mulai mendaftarkan siswa dan mengunggah materi evaluasi.</p>
            <button type="button" onclick="openCreateModal()" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700">
                + Buat Kelas Pertama Anda
            </button>
        </div>
    @endif
</div>

{{-- Modal Popup Buat Kelas Baru --}}
<div id="create-class-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-xs hidden" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                </span>
                <h3 id="modal-title" class="text-lg font-bold text-slate-900">Buat Mata Pelajaran Baru</h3>
            </div>
            <button type="button" onclick="closeCreateModal()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('teacher.subjects.store') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="create-classroom" class="block text-sm font-semibold text-slate-700">Rombel sekolah <span class="text-rose-500">*</span></label>
                <select id="create-classroom" name="classroom_id" required class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 outline-none transition focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100" @disabled($classrooms->isEmpty())>
                    <option value="">Pilih rombel</option>
                    @foreach ($classrooms as $classroom)
                        <option value="{{ $classroom->id }}">{{ $classroom->name }} · {{ $classroom->academic_year }}</option>
                    @endforeach
                </select>
                @if ($classrooms->isEmpty())<p class="mt-1.5 text-sm text-amber-800">Belum ada rombel. Hubungi admin sekolah untuk membuatnya.</p>@endif
            </div>
            <div>
                <label for="create-name" class="block text-sm font-semibold text-slate-700">Nama Mata Pelajaran / Kelas <span class="text-rose-500">*</span></label>
                <input type="text" id="create-name" name="name" required maxlength="120" placeholder="Contoh: Matematika Peminatan XII IPA 1" class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div>
                <label for="create-description" class="block text-sm font-semibold text-slate-700">Deskripsi Kelas <span class="font-normal text-slate-400">(opsional)</span></label>
                <textarea id="create-description" name="description" rows="3" maxlength="1000" placeholder="Jelaskan gambaran kelas atau capaian pembelajaran..." class="mt-1.5 block w-full rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100"></textarea>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4">
                <button type="button" onclick="closeCreateModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                    Batal
                </button>
                <button type="submit" @disabled($classrooms->isEmpty()) class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                    Simpan & Masuk ke Kelas
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        const modal = document.getElementById('create-class-modal');
        if (modal) {
            modal.classList.remove('hidden');
            setTimeout(() => document.getElementById('create-name')?.focus(), 50);
        }
    }

    function closeCreateModal() {
        const modal = document.getElementById('create-class-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Live search filter for subject cards
    const filterInput = document.getElementById('filter-subjects-input');
    if (filterInput) {
        filterInput.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const cards = document.getElementsByClassName('subject-card');
            let matchCount = 0;

            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getAttribute('data-name') || '';
                const desc = cards[i].getAttribute('data-desc') || '';
                if (name.includes(term) || desc.includes(term)) {
                    cards[i].style.display = '';
                    matchCount++;
                } else {
                    cards[i].style.display = 'none';
                }
            }

            const label = document.getElementById('subject-count-label');
            if (label) {
                label.textContent = term ? `Ditemukan ${matchCount} kelas` : `Menampilkan ${cards.length} kelas`;
            }
        });
    }
</script>
@endsection
