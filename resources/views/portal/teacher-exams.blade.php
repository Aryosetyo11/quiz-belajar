@extends('layouts.portal')

@section('title', 'Sesi Ujian | RuangUji')

@section('content')
    <header class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Evaluasi pembelajaran</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Sesi ujian</h1>
            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Susun sesi ujian dari paket Bank Soal. Sistem mengacak soal bagi setiap siswa dengan jumlah esai yang tetap.</p>
        </div>
        <a href="{{ route('teacher.exam-sessions.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-indigo-800">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Buat ujian
        </a>
    </header>

    @forelse ($subjects as $subject)
        <section class="mt-8" aria-labelledby="subject-{{ $subject->id }}">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="subject-{{ $subject->id }}" class="text-xl font-semibold text-slate-950">{{ $subject->name }} <span class="font-medium text-slate-500">· {{ $subject->classroom?->name ?? 'Rombel belum diatur' }}</span></h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $subject->school?->name ?? 'Sekolah' }} · {{ $subject->classroom?->academic_year ?? 'Tahun ajaran belum diatur' }}</p>
                </div>
                @if ($subject->bank_package_count > 0)
                    <a href="{{ route('teacher.exam-sessions.create', ['subject_id' => $subject->id]) }}" class="inline-flex items-center gap-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3.5 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100">Buat ujian mapel ini <span aria-hidden="true">&rarr;</span></a>
                @endif
            </div>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @forelse ($subject->exams as $exam)
                    <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="text-base font-bold text-slate-950">{{ $exam->title }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $exam->question_count ?? $exam->questions_count }} soal · {{ $exam->essay_question_count ?? 0 }} esai · {{ $exam->duration_minutes }} menit</p>
                            </div>
                            <span @class(['shrink-0 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold', 'bg-slate-100 text-slate-700' => $exam->isArchived(), 'bg-emerald-100 text-emerald-800' => ! $exam->isArchived() && $exam->is_published, 'bg-amber-100 text-amber-800' => ! $exam->isArchived() && ! $exam->is_published])>
                                @if ($exam->isArchived())
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-500"></span>Diarsipkan
                                @elseif ($exam->is_published)
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Dipublikasikan
                                @else
                                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Draf
                                @endif
                            </span>
                        </div>

                        @if ($exam->description)<p class="mt-2 text-sm leading-6 text-slate-600">{{ Str::limit($exam->description, 80) }}</p>@endif
                        @if ($exam->bankPackage)<p class="mt-2 text-sm text-slate-500">Sumber: {{ $exam->bankPackage->title }}</p>@endif
                        <p class="mt-2 text-sm text-slate-400">{{ $exam->starts_at ? 'Mulai '.$exam->starts_at->format('d M Y H:i') : 'Jadwal fleksibel' }}{{ $exam->ends_at ? ' · Berakhir '.$exam->ends_at->format('d M Y H:i') : '' }}</p>

                        {{-- Stats --}}
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 px-3 py-2.5 text-center">
                                <p class="text-xl font-bold text-slate-900">{{ $exam->attempts_count }}</p>
                                <p class="mt-0.5 text-sm text-slate-500">Pengerjaan</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 px-3 py-2.5 text-center">
                                <p class="text-xl font-bold text-slate-900">{{ $exam->average_score !== null ? number_format($exam->average_score, 1) : '—' }}</p>
                                <p class="mt-0.5 text-sm text-slate-500">Rata-rata nilai</p>
                            </div>
                        </div>

                        {{-- Publish toggle --}}
                        <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-slate-100 pt-4">
                            @if ($exam->isArchived())
                                <form method="POST" action="{{ route('teacher.exam-sessions.restore', $exam) }}" data-confirm="Pulihkan sesi ini sebagai draf? Riwayat pengerjaan tetap tersimpan dan siswa belum bisa mengakses sampai sesi diterbitkan kembali." data-confirm-title="Pulihkan sesi?" data-confirm-btn="Pulihkan sesi" data-confirm-variant="primary">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-800 transition hover:bg-indigo-100">Pulihkan sebagai draf</button>
                                </form>
                                <p class="text-sm text-slate-500">Diarsipkan {{ $exam->archived_at?->format('d M Y') }}{{ $exam->archivedBy ? ' oleh '.$exam->archivedBy->name : '' }}.</p>
                            @else
                                @if ($exam->is_published && $exam->active_attempts_count > 0)
                                    <p class="text-sm text-slate-500">Sesi tetap berjalan sampai {{ $exam->active_attempts_count }} pengerjaan aktif selesai.</p>
                                @else
                                    <form method="POST" action="{{ route('teacher.exam-sessions.toggle-publish', $exam) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-semibold transition {{ $exam->is_published ? 'border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                            {{ $exam->is_published ? 'Jadikan draf' : 'Publikasikan' }}
                                        </button>
                                    </form>
                                @endif

                                @if (! $exam->is_published && $exam->attempts_count === 0)
                                    <form method="POST" action="{{ route('teacher.exam-sessions.destroy', $exam) }}" data-confirm="Hapus sesi draf &quot;{{ $exam->title }}&quot;? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus sesi ujian?" data-confirm-btn="Hapus sesi" data-confirm-variant="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">Hapus draf</button>
                                    </form>
                                @elseif ($exam->active_attempts_count === 0)
                                    <form method="POST" action="{{ route('teacher.exam-sessions.archive', $exam) }}" data-confirm="Arsipkan sesi &quot;{{ $exam->title }}&quot;? Sesi akan berhenti muncul untuk siswa, tetapi nilai dan riwayat pengerjaan tetap tersedia." data-confirm-title="Arsipkan sesi ujian?" data-confirm-btn="Arsipkan sesi" data-confirm-variant="primary">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Arsipkan</button>
                                    </form>
                                @else
                                    <p class="text-sm text-slate-500">Arsip tersedia setelah pengerjaan aktif selesai.</p>
                                @endif
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6">
                        @if ($subject->bank_package_count > 0)
                            <p class="text-sm text-slate-600">Belum ada sesi ujian untuk mapel ini.</p>
                            <a href="{{ route('teacher.exam-sessions.create', ['subject_id' => $subject->id]) }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Buat sesi dari paket soal &rarr;</a>
                        @else
                            <p class="text-sm text-slate-600">Belum ada paket soal untuk mapel ini.</p>
                            <a href="{{ route('teacher.question-bank', ['subject_id' => $subject->id]) }}" class="mt-3 inline-flex text-sm font-semibold text-indigo-700 underline-offset-4 hover:underline">Buat paket di Bank Soal &rarr;</a>
                        @endif
                    </div>
                @endforelse
            </div>
        </section>
    @empty
        <section class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/></svg>
            <h2 class="mt-4 text-lg font-bold text-slate-950">Buat mata pelajaran lebih dahulu</h2>
            <p class="mt-2 text-sm text-slate-600">Ujian harus terhubung dengan mata pelajaran yang Anda kelola.</p>
            <a href="{{ route('teacher.subjects') }}" class="mt-5 inline-flex items-center gap-2 rounded-xl bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">Kelola mata pelajaran</a>
        </section>
    @endforelse
@endsection
