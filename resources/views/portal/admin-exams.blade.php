@extends('layouts.portal')

@section('title', 'Sesi Ujian | All Remote')

@section('content')
    <div class="space-y-6">
        <header data-reveal>
            <p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Pengawasan platform</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Sesi ujian</h1>
            <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Tinjau sesi lintas sekolah. Hapus hanya draf yang belum digunakan; arsipkan sesi yang sudah memiliki riwayat agar nilai tetap tersimpan.</p>
        </header>

        <form method="GET" action="{{ route('admin.exam-sessions') }}" class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[minmax(220px,1fr)_minmax(180px,0.7fr)_auto_auto] sm:items-end" data-reveal>
            <label class="text-sm font-semibold text-slate-700">Sekolah
                <select name="school_id" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                    <option value="">Semua sekolah</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected($filters['school_id'] === $school->id)>{{ $school->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm font-semibold text-slate-700">Status sesi
                <select name="status" class="mt-1.5 block w-full rounded-xl border-slate-300 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                    <option value="all" @selected($filters['status'] === 'all')>Semua status</option>
                    <option value="published" @selected($filters['status'] === 'published')>Terbit</option>
                    <option value="draft" @selected($filters['status'] === 'draft')>Draf</option>
                    <option value="archived" @selected($filters['status'] === 'archived')>Arsip</option>
                </select>
            </label>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Terapkan filter</button>
            <a href="{{ route('admin.exam-sessions') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
        </form>

        @if ($sessions->isEmpty())
            <section class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center" data-reveal>
                <h2 class="text-lg font-bold text-slate-950">Belum ada sesi pada filter ini</h2>
                <p class="mt-2 text-sm text-slate-600">Coba pilih sekolah atau status yang berbeda.</p>
            </section>
        @else
            <section class="grid gap-4 xl:grid-cols-2" aria-label="Daftar sesi ujian">
                @foreach ($sessions as $exam)
                    <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" data-reveal>
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="text-lg font-bold text-slate-950">{{ $exam->title }}</h2>
                                <p class="mt-1 text-sm text-slate-600">{{ $exam->subject?->name ?? 'Mapel tidak tersedia' }} · {{ $exam->subject?->classroom?->name ?? 'Rombel tidak tersedia' }}</p>
                            </div>
                            <span @class(['shrink-0 rounded-full px-3 py-1 text-xs font-bold', 'bg-slate-100 text-slate-700' => $exam->isArchived(), 'bg-emerald-100 text-emerald-800' => ! $exam->isArchived() && $exam->is_published, 'bg-amber-100 text-amber-800' => ! $exam->isArchived() && ! $exam->is_published])>{{ $exam->isArchived() ? 'Diarsipkan' : ($exam->is_published ? 'Terbit' : 'Draf') }}</span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-sm font-semibold text-slate-800">{{ $exam->subject?->school?->name ?? 'Sekolah tidak tersedia' }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ $exam->subject?->teacher?->name ?? 'Pengajar tidak tersedia' }} · {{ $exam->subject?->classroom?->academic_year ?? 'Tahun ajaran tidak tersedia' }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="rounded-xl border border-slate-200 p-3"><p class="text-xl font-bold tabular-nums text-slate-950">{{ number_format($exam->attempts_count, 0, ',', '.') }}</p><p class="text-xs font-medium text-slate-500">Riwayat</p></div>
                                <div class="rounded-xl border border-slate-200 p-3"><p class="text-xl font-bold tabular-nums text-slate-950">{{ number_format($exam->active_attempts_count, 0, ',', '.') }}</p><p class="text-xs font-medium text-slate-500">Sedang dikerjakan</p></div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                            @if ($exam->isArchived())
                                <form method="POST" action="{{ route('admin.exam-sessions.restore', $exam) }}" data-confirm="Pulihkan sesi ini sebagai draf? Riwayat pengerjaan tetap tersimpan dan siswa belum dapat mengakses sampai sesi diterbitkan kembali." data-confirm-title="Pulihkan sesi?" data-confirm-btn="Pulihkan sesi" data-confirm-variant="primary">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100">Pulihkan sebagai draf</button>
                                </form>
                                <p class="text-sm text-slate-500">Diarsipkan {{ $exam->archived_at?->format('d M Y') }}{{ $exam->archivedBy ? ' oleh '.$exam->archivedBy->name : '' }}.</p>
                            @elseif (! $exam->is_published && $exam->attempts_count === 0)
                                <form method="POST" action="{{ route('admin.exam-sessions.destroy', $exam) }}" data-confirm="Hapus draf &quot;{{ $exam->title }}&quot;? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus sesi ujian?" data-confirm-btn="Hapus sesi" data-confirm-variant="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Hapus draf</button>
                                </form>
                            @elseif ($exam->active_attempts_count === 0)
                                <form method="POST" action="{{ route('admin.exam-sessions.archive', $exam) }}" data-confirm="Arsipkan sesi &quot;{{ $exam->title }}&quot;? Sesi tidak lagi muncul untuk siswa, tetapi nilai dan riwayatnya tetap disimpan." data-confirm-title="Arsipkan sesi ujian?" data-confirm-btn="Arsipkan sesi" data-confirm-variant="primary">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Arsipkan</button>
                                </form>
                            @else
                                <p class="text-sm text-slate-500">Arsip tersedia setelah pengerjaan aktif selesai.</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <div>{{ $sessions->links() }}</div>
        @endif
    </div>
@endsection
