@extends('layouts.portal')

@section('title', $subject->name.' | Ruang Kelas Pengajar')

@section('content')
@php
    $activeTab = request('tab', 'students');
@endphp

<div class="space-y-6">
    {{-- Back Link & Breadcrumbs --}}
    <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
        <a href="{{ route('teacher.subjects') }}" class="inline-flex items-center gap-1.5 text-slate-600 hover:text-indigo-600 transition-colors">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
            Semua Mata Pelajaran
        </a>
        <span>/</span>
        <span class="text-slate-800 font-semibold truncate">{{ $subject->name }}</span>
    </div>

    {{-- Class Banner Hero --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/90 bg-white p-6 sm:p-8 shadow-2xs">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-5">
            <div class="flex items-start gap-4 min-w-0">
                <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-2xl font-bold text-white shadow-sm">
                    {{ mb_substr($subject->name, 0, 1) }}
                </span>
                <div class="min-w-0">
                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10">Ruang Kelas</span>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{{ $subject->name }}</h1>
                    <p class="mt-1 text-sm sm:text-base text-slate-500 leading-relaxed">{{ $subject->description ?: 'Belum ada deskripsi untuk kelas ini.' }}</p>
                    @if ($subject->classroom)
                        <p class="mt-2 text-sm font-semibold text-indigo-700">Rombel {{ $subject->classroom->name }} · {{ $subject->classroom->academic_year }}</p>
                    @endif
                    @if ($subject->schedules->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($subject->schedules as $schedule)
                                <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700">{{ [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$schedule->day_of_week] }} · {{ substr($schedule->starts_at, 0, 5) }}–{{ substr($schedule->ends_at, 0, 5) }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Metric Pills --}}
            <div class="flex flex-wrap items-center gap-2 sm:self-start">
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-bold text-slate-700">
                    <svg class="h-3.5 w-3.5 text-teal-600" viewBox="0 0 20 20" fill="currentColor"><path d="M7 8a3 3 0 100-6 3 3 0 000 6zM14.5 9a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM1.615 16.428a1.224 1.224 0 01-.569-1.175 6.002 6.002 0 0111.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 017 18a9.953 9.953 0 01-5.385-1.572zM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 00-1.588-3.755 4.502 4.502 0 015.874 4.163.75.75 0 01-.549.525z"/></svg>
                    {{ $subject->students->count() }} Siswa
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-bold text-slate-700">
                    <svg class="h-3.5 w-3.5 text-indigo-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0010.378 2H4.5zm5.75 5.5v-4.5L15 7.5h-4.75z" clip-rule="evenodd"/></svg>
                    {{ $subject->materials->count() }} Materi
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm font-bold text-slate-700">
                    <svg class="h-3.5 w-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    {{ $subject->exams->count() }} Ujian
                </span>
            </div>
        </div>
    </div>

    {{-- Class Tab Navigation --}}
    <div class="border-b border-slate-200">
        <nav class="flex space-x-2 sm:space-x-4" aria-label="Tabs Ruang Kelas">
            <button type="button" onclick="selectClassTab('students')" id="tab-nav-students" class="class-tab-btn flex items-center gap-2 border-b-2 py-3 px-3 text-sm sm:text-base font-bold transition-colors {{ $activeTab === 'students' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0Zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0Z"/></svg>
                Siswa Peserta
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-sm font-bold text-slate-600">{{ $subject->students->count() }}</span>
            </button>

            <button type="button" onclick="selectClassTab('materials')" id="tab-nav-materials" class="class-tab-btn flex items-center gap-2 border-b-2 py-3 px-3 text-sm sm:text-base font-bold transition-colors {{ $activeTab === 'materials' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                Materi Belajar
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-sm font-bold text-slate-600">{{ $subject->materials->count() }}</span>
            </button>

            <button type="button" onclick="selectClassTab('exams')" id="tab-nav-exams" class="class-tab-btn flex items-center gap-2 border-b-2 py-3 px-3 text-sm sm:text-base font-bold transition-colors {{ $activeTab === 'exams' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Evaluasi & Ujian
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-sm font-bold text-slate-600">{{ $subject->exams->count() }}</span>
            </button>

            <button type="button" onclick="selectClassTab('settings')" id="tab-nav-settings" class="class-tab-btn flex items-center gap-2 border-b-2 py-3 px-3 text-sm sm:text-base font-bold transition-colors {{ $activeTab === 'settings' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-800' }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Pengaturan
            </button>
        </nav>
    </div>

    {{-- TAB 1: SISWA PESERTA --}}
    <div id="tab-content-students" class="space-y-4 {{ $activeTab === 'students' ? '' : 'hidden' }}">
        {{-- Action Bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative max-w-md flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" id="class-students-search" placeholder="Cari siswa di kelas ini (nama atau NISN)..." class="block w-full rounded-xl border border-slate-200/90 bg-white py-2.5 pl-10 pr-4 text-sm sm:text-base text-slate-900 placeholder:text-slate-400 shadow-2xs outline-none transition focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
            </div>

            <div class="flex items-center gap-2">
                <button type="button" onclick="openEnrollModal()" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm sm:text-base font-semibold text-white shadow-xs transition hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    + Daftarkan Siswa ke Kelas
                </button>
            </div>
        </div>

        @if ($subject->students->isNotEmpty())
            <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm sm:text-base text-slate-600">
                        <thead class="border-b border-slate-100 bg-slate-50/70 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <tr>
                                <th scope="col" class="py-3.5 pl-5 pr-3">Siswa</th>
                                <th scope="col" class="px-3 py-3.5">NISN</th>
                                <th scope="col" class="px-3 py-3.5 hidden md:table-cell">Email Akun</th>
                                <th scope="col" class="py-3.5 pl-3 pr-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="class-students-tbody" class="divide-y divide-slate-100">
                            @foreach ($subject->students as $student)
                                <tr class="class-student-row hover:bg-slate-50/60 transition-colors" data-name="{{ strtolower($student->name) }}" data-nisn="{{ $student->nisn }}">
                                    <td class="py-3.5 pl-5 pr-3">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-50 text-xs font-bold text-teal-700 ring-1 ring-inset ring-teal-600/10">
                                                {{ mb_substr($student->name, 0, 1) }}
                                            </span>
                                            <div>
                                                <span class="font-bold text-slate-900 block truncate">{{ $student->name }}</span>
                                                <span class="text-sm text-slate-400 md:hidden">NISN: {{ $student->nisn }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3.5">
                                        <span class="font-mono text-sm font-semibold text-slate-700 rounded-md bg-slate-100 px-2 py-0.5">
                                            {{ $student->nisn }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 hidden md:table-cell text-slate-500">
                                        {{ $student->email ?: '—' }}
                                    </td>
                                    <td class="py-3.5 pl-3 pr-5 text-right">
                                        <form method="POST" action="{{ route('teacher.subjects.students.destroy', [$subject, $student]) }}" data-confirm="Keluarkan {{ $student->name }} dari rombel {{ $subject->classroom->name ?? $subject->name }}? Siswa akan kehilangan akses ke semua mapel dan ujian rombel ini." data-confirm-title="Keluarkan dari rombel?" data-confirm-btn="Ya, Keluarkan" data-confirm-variant="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50/50 px-2.5 py-1 text-sm font-semibold text-rose-700 transition hover:bg-rose-100 hover:border-rose-300">
                                                Keluarkan dari rombel
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-slate-100 px-5 py-3 text-sm text-slate-500 flex items-center justify-between">
                    <span id="students-search-count">Total {{ $subject->students->count() }} siswa terdaftar</span>
                    <span class="text-sm text-slate-400">Siswa dapat mengakses materi dan ujian kelas ini</span>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-2xs">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <h3 class="mt-3 text-sm font-bold text-slate-800">Belum Ada Siswa di Kelas Ini</h3>
                <p class="mx-auto mt-1 max-w-sm text-sm text-slate-500 leading-relaxed">Daftarkan siswa ke dalam kelas ini agar mereka dapat melihat materi dan mengerjakan ujian kuis yang Anda rilis.</p>
                <button type="button" onclick="openEnrollModal()" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-700">
                    + Daftarkan Siswa Sekarang
                </button>
            </div>
        @endif
    </div>

    {{-- TAB 2: MATERI BELAJAR --}}
    <div id="tab-content-materials" class="space-y-4 {{ $activeTab === 'materials' ? '' : 'hidden' }}">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Materi Pembelajaran</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Padukan modul untuk rangkuman dan video untuk penjelasan. Siswa dapat membuka keduanya dari halaman belajar.</p>
            </div>
            <a href="{{ route('teacher.subjects.materials', $subject) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-700">
                + Kelola & Upload Materi
            </a>
        </div>

        @if ($subject->materials->isNotEmpty())
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($subject->materials as $material)
                    <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-200/90 bg-white p-4 shadow-2xs hover:border-slate-300 transition">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $material->isVideo() ? 'bg-rose-50 text-rose-700' : ($material->isExternalLink() ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700') }}">
                                    {{ $material->isVideo() ? 'Video' : ($material->isExternalLink() ? 'Drive / Canva' : 'Dokumen') }}
                                </span>
                                <h3 class="truncate text-sm sm:text-base font-bold text-slate-900">{{ $material->title }}</h3>
                            </div>
                            <p class="mt-1 text-sm text-slate-500 truncate">{{ $material->description ?: ($material->isVideo() ? 'Tautan video pembelajaran' : ($material->isExternalLink() ? 'Materi dari Google Drive atau Canva' : $material->original_file_name)) }}</p>
                        </div>
                        <div class="shrink-0">
                            @if ($material->isVideo())
                                <a href="{{ $material->video_url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-slate-200 px-2.5 py-1 text-sm font-semibold text-slate-700 hover:bg-slate-50">Tonton</a>
                            @elseif ($material->isExternalLink())
                                <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-slate-200 px-2.5 py-1 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buka</a>
                            @else
                                <a href="{{ route('subject-materials.download', [$subject, $material]) }}" class="rounded-lg border border-slate-200 px-2.5 py-1 text-sm font-semibold text-slate-700 hover:bg-slate-50">Unduh</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-2xs">
                <p class="text-base font-semibold text-slate-900">Belum ada materi pembelajaran</p>
                <p class="mx-auto mt-1 max-w-lg text-sm leading-6 text-slate-600">Mulai dengan mengunggah modul atau menambahkan video YouTube/Vimeo. Menggabungkan ringkasan tertulis dan penjelasan visual dapat membantu siswa belajar dengan cara yang berbeda.</p>
                <a href="{{ route('teacher.subjects.materials', $subject) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3.5 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                    + Upload Materi Pertama
                </a>
            </div>
        @endif
    </div>

    {{-- TAB 3: EVALUASI & UJIAN --}}
    <div id="tab-content-exams" class="space-y-4 {{ $activeTab === 'exams' ? '' : 'hidden' }}">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Ujian & Kuis Kelas</h2>
                <p class="text-sm text-slate-500">Evaluasi digital yang disiapkan khusus untuk mata pelajaran ini</p>
            </div>
            <a href="{{ route('teacher.exam-sessions.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-700">
                + Buat Ujian Baru
            </a>
        </div>

        @if ($subject->exams->isNotEmpty())
            <div class="space-y-3">
                @foreach ($subject->exams as $exam)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-slate-200/90 bg-white p-4 shadow-2xs">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-slate-900 truncate">{{ $exam->title }}</h3>
                                <span class="rounded-full px-2 py-0.5 text-xs font-bold {{ $exam->is_published ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ $exam->is_published ? 'Aktif' : 'Draf' }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500">{{ $exam->question_count ?? $exam->questions_count }} soal · {{ $exam->essay_question_count ?? 0 }} esai · {{ $exam->duration_minutes }} menit · {{ $exam->attempts_count }} pengerjaan siswa</p>
                        </div>
                        <div class="shrink-0 flex items-center gap-2">
                            <a href="{{ route('teacher.exam-sessions') }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Kelola di Sesi Ujian &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-2xs">
                <p class="text-xs font-semibold text-slate-800">Belum ada ujian yang dibuat untuk mata pelajaran ini</p>
                <p class="mt-1 text-sm text-slate-400">Buat paket soal di Bank Soal, lalu pilih paket tersebut saat menyusun sesi ujian.</p>
                <a href="{{ route('teacher.exam-sessions.create') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    + Buat Sesi Ujian
                </a>
            </div>
        @endif
    </div>

    {{-- TAB 4: PENGATURAN KELAS --}}
    <div id="tab-content-settings" class="space-y-6 {{ $activeTab === 'settings' ? '' : 'hidden' }}">
        {{-- Edit Subject Form --}}
        <section class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs" aria-labelledby="settings-heading">
            <h2 id="settings-heading" class="text-sm font-bold text-slate-900">Informasi Mata Pelajaran</h2>
            <p class="text-sm text-slate-500">Perbarui nama dan deskripsi ruang kelas ini.</p>

            <form method="POST" action="{{ route('teacher.subjects.update', $subject) }}" class="mt-4 space-y-4 max-w-xl">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700">Nama Mata Pelajaran</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $subject->name) }}" required maxlength="120" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm sm:text-base text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                </div>
                <div>
                    <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi / Capaian Pembelajaran</label>
                    <textarea id="description" name="description" rows="3" maxlength="1000" class="mt-1 block w-full rounded-xl border border-slate-300 px-3.5 py-2 text-sm sm:text-base text-slate-900 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">{{ old('description', $subject->description) }}</textarea>
                </div>
                <div>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>

        {{-- Danger Zone: Hapus Kelas --}}
        <section class="rounded-2xl border border-rose-200 bg-rose-50/40 p-6">
            <h2 class="text-sm font-bold text-rose-900">Zona Bahaya</h2>
            <p class="mt-0.5 text-sm text-rose-700">Menghapus mata pelajaran ini akan melepaskan seluruh pendaftaran siswa dan materi terkait.</p>
            <form method="POST" action="{{ route('teacher.subjects.destroy', $subject) }}" data-confirm="Mata pelajaran {{ $subject->name }} beserta seluruh materi dan pendaftaran siswa akan dihapus permanen. Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus Mata Pelajaran?" data-confirm-btn="Ya, Hapus Permanen" data-confirm-variant="danger" class="mt-4">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-600 hover:text-white transition">
                    Hapus Mata Pelajaran Ini
                </button>
            </form>
        </section>
    </div>
</div>

{{-- MODAL DAFTARKAN SISWA KE KELAS --}}
<div id="enroll-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-xs hidden" role="dialog" aria-modal="true" aria-labelledby="enroll-modal-title">
    <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-xl transition-all">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </span>
                <div>
                    <h3 id="enroll-modal-title" class="text-base font-bold text-slate-900">Daftarkan Siswa ke Kelas</h3>
                    <p class="text-sm text-slate-500">Siswa boleh terdaftar di beberapa rombel dalam sekolah yang sama.</p>
                </div>
            </div>
            <button type="button" onclick="closeEnrollModal()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="mt-4 space-y-4">
            {{-- Metode 1: Cari dari Direktori Sekolah --}}
            <div>
                <label for="enroll-search-input" class="block text-sm font-bold text-slate-700">1. Pilih siswa yang belum terdaftar di rombel ini</label>
                <div class="relative mt-1.5">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" id="enroll-search-input" oninput="filterAvailableStudents(this.value)" placeholder="Ketik nama siswa atau NISN..." class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2 pl-9 pr-3 text-sm text-slate-900 placeholder:text-slate-400 outline-none focus:border-indigo-600 focus:bg-white focus:ring-2 focus:ring-indigo-100">
                </div>

                {{-- Loaded dynamically via AJAX --}}
                <div id="available-students-wrap" class="mt-2.5 max-h-52 overflow-y-auto rounded-xl border border-slate-200 bg-white divide-y divide-slate-100 hidden">
                    <ul id="available-students-list"></ul>
                </div>
                <div id="available-students-loading" class="mt-2 hidden rounded-xl border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                    <svg class="mx-auto mb-1 h-4 w-4 animate-spin text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    Memuat daftar siswa...
                </div>
                <div id="available-students-empty" class="mt-2 hidden rounded-xl border border-slate-200 bg-slate-50 p-4 text-center text-sm text-slate-500">
                    Semua siswa terdaftar di sekolah sudah tergabung di kelas ini.
                </div>
                <div id="available-students-error" class="mt-2 hidden rounded-xl border border-rose-200 bg-rose-50 p-4 text-center text-sm text-rose-700">
                    Gagal memuat daftar siswa. Silakan coba lagi.
                </div>
            </div>

            <div class="relative flex items-center justify-center">
                <div class="border-t border-slate-200 w-full"></div>
                <span class="bg-white px-3 text-xs font-bold uppercase tracking-wider text-slate-400 absolute">Atau</span>
            </div>

            {{-- Metode 2: Input NISN Manual --}}
            <div>
                <label for="manual-nisn" class="block text-sm font-bold text-slate-700">2. Masukkan NISN 10 Digit Secara Manual</label>
                <form method="POST" action="{{ route('teacher.subjects.students.store', $subject) }}" class="mt-1.5 flex gap-2">
                    @csrf
                    <input type="text" id="manual-nisn" name="nisn" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" placeholder="Contoh: 0071234567" required class="block flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 outline-none focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-2xs hover:bg-slate-800 transition">
                        Daftarkan
                    </button>
                </form>
            </div>
        </div>

        <div class="mt-5 border-t border-slate-100 pt-3 text-right">
            <button type="button" onclick="closeEnrollModal()" class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    // Tab switching in classroom detail view
    function selectClassTab(tab) {
        const tabs = ['students', 'materials', 'exams', 'settings'];
        tabs.forEach(t => {
            const content = document.getElementById('tab-content-' + t);
            const navBtn = document.getElementById('tab-nav-' + t);
            if (t === tab) {
                content?.classList.remove('hidden');
                navBtn?.classList.remove('border-transparent', 'text-slate-500');
                navBtn?.classList.add('border-indigo-600', 'text-indigo-600');
            } else {
                content?.classList.add('hidden');
                navBtn?.classList.remove('border-indigo-600', 'text-indigo-600');
                navBtn?.classList.add('border-transparent', 'text-slate-500');
            }
        });

        // Update URL query param cleanly without reload
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.replaceState({}, '', url);
    }

    // Modal enroll student — lazy loads available students on first open
    const availableStudentsUrl = '{{ route('teacher.subjects.students.available', $subject) }}';
    const enrollStudentUrl = '{{ route('teacher.subjects.students.store', $subject) }}';
    let studentsLoaded = false;
    let allAvailableStudents = [];

    function openEnrollModal() {
        const modal = document.getElementById('enroll-modal');
        if (!modal) { return; }
        modal.classList.remove('hidden');
        setTimeout(() => document.getElementById('enroll-search-input')?.focus(), 50);

        if (!studentsLoaded) {
            loadAvailableStudents();
        }
    }

    function loadAvailableStudents() {
        const loadingEl = document.getElementById('available-students-loading');
        const wrapEl    = document.getElementById('available-students-wrap');
        const emptyEl   = document.getElementById('available-students-empty');
        const errorEl   = document.getElementById('available-students-error');

        loadingEl?.classList.remove('hidden');
        wrapEl?.classList.add('hidden');
        emptyEl?.classList.add('hidden');
        errorEl?.classList.add('hidden');

        fetch(availableStudentsUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) { throw new Error('Network error'); }
            return res.json();
        })
        .then(students => {
            studentsLoaded = true;
            allAvailableStudents = students;
            loadingEl?.classList.add('hidden');

            if (students.length === 0) {
                emptyEl?.classList.remove('hidden');
                return;
            }

            renderAvailableStudents(students);
            wrapEl?.classList.remove('hidden');
        })
        .catch(() => {
            loadingEl?.classList.add('hidden');
            errorEl?.classList.remove('hidden');
        });
    }

    function renderAvailableStudents(students) {
        const list = document.getElementById('available-students-list');
        if (!list) { return; }

        list.innerHTML = students.map(student => {
            const initial = student.name.charAt(0).toUpperCase();
            const nisnDisplay = student.nisn ?? '—';
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            return `<li class="candidate-row flex items-center justify-between gap-3 p-2.5 hover:bg-indigo-50/40 transition"
                        data-name="${student.name.toLowerCase()}"
                        data-nisn="${student.nisn ?? ''}">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-700">${initial}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800">${student.name}</p>
                                <p class="text-sm text-slate-400">NISN: <span class="font-mono text-slate-600 font-medium">${nisnDisplay}</span></p>
                            </div>
                        </div>
                        <form method="POST" action="${enrollStudentUrl}">
                            <input type="hidden" name="_token" value="${csrf}">
                            <input type="hidden" name="student_id" value="${student.id}">
                            <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-2xs hover:bg-indigo-700 transition">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Tambahkan
                            </button>
                        </form>
                    </li>`;
        }).join('');
    }

    function closeEnrollModal() {
        const modal = document.getElementById('enroll-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Filter students enrolled in class table
    const classStudentsSearch = document.getElementById('class-students-search');
    if (classStudentsSearch) {
        classStudentsSearch.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            const rows = document.getElementsByClassName('class-student-row');
            let matchCount = 0;

            for (let i = 0; i < rows.length; i++) {
                const name = rows[i].getAttribute('data-name') || '';
                const nisn = rows[i].getAttribute('data-nisn') || '';
                if (name.includes(term) || nisn.includes(term)) {
                    rows[i].style.display = '';
                    matchCount++;
                } else {
                    rows[i].style.display = 'none';
                }
            }

            const countLabel = document.getElementById('students-search-count');
            if (countLabel) {
                countLabel.textContent = term ? `Ditemukan: ${matchCount} siswa` : `Total ${rows.length} siswa terdaftar`;
            }
        });
    }

    // Filter available candidates in enroll modal (works on in-memory data after AJAX load)
    function filterAvailableStudents(term) {
        const query = term.toLowerCase().trim();

        if (!studentsLoaded) { return; }

        const wrapEl  = document.getElementById('available-students-wrap');
        const emptyEl = document.getElementById('available-students-empty');

        const filtered = query
            ? allAvailableStudents.filter(s =>
                s.name.toLowerCase().includes(query) ||
                (s.nisn ?? '').includes(query)
              )
            : allAvailableStudents;

        if (filtered.length === 0 && allAvailableStudents.length > 0) {
            wrapEl?.classList.add('hidden');
            emptyEl?.classList.remove('hidden');
            emptyEl.textContent = `Tidak ada siswa yang cocok dengan "${term}".`;
        } else if (allAvailableStudents.length === 0) {
            wrapEl?.classList.add('hidden');
            emptyEl?.classList.remove('hidden');
        } else {
            wrapEl?.classList.remove('hidden');
            emptyEl?.classList.add('hidden');
            renderAvailableStudents(filtered);
        }
    }
</script>
@endsection
