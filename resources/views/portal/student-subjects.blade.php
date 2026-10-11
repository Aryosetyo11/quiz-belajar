@extends('layouts.portal')

@section('title', 'Mata Pelajaran | RuangUji')

@section('content')
    <header>
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Pembelajaran saya</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Mata Pelajaran</h1>
        <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Daftar mata pelajaran yang diberikan pengajar kepada akun Anda.</p>
    </header>

    <section class="mt-8" aria-label="Daftar mata pelajaran">
        @forelse ($subjects as $subject)
            <article class="mb-4 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">{{ $subject->name }}</h2>
                        <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">{{ $subject->description ?: 'Belum ada deskripsi untuk mata pelajaran ini.' }}</p>
                    </div>
                    <div class="rounded-lg bg-slate-50 px-4 py-3 sm:min-w-48">
                        <p class="text-sm text-slate-500">Pengajar</p>
                        <p class="mt-1 text-base font-medium text-slate-800">{{ $subject->teacher->name }}</p>
                    </div>
                </div>
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <p class="text-sm text-slate-600">{{ $subject->materials_count }} materi tersedia</p>
                    <a href="{{ route('student.subjects.show', $subject) }}" class="rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Buka materi</a>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-8">
                <h2 class="text-xl font-semibold text-slate-950">Belum ada mata pelajaran</h2>
                <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Mata pelajaran akan tampil setelah pengajar mendaftarkan akun Anda. Pastikan NISN di profil sudah benar.</p>
                <a href="{{ route('profile.edit') }}" class="mt-6 inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50">Periksa profil</a>
            </div>
        @endforelse
    </section>
@endsection
