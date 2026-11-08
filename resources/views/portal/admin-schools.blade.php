@extends('layouts.portal')

@section('title', 'Sekolah dan Rombel')

@section('content')
<header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Pengelolaan institusi</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Sekolah & rombel</h1>
        <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Pisahkan akun dan kegiatan belajar berdasarkan sekolah, tahun ajaran, serta rombongan belajar.</p>
        <p class="mt-2 text-sm leading-6 text-slate-500">Data mapel lama ditautkan ke rombel sementara berlabel “Data awal”. Periksa dan ubah nama rombelnya sesuai struktur sekolah.</p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        <button type="button" data-open-school-modal class="inline-flex w-fit items-center rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-indigo-100">Tambah sekolah</button>
        <a href="{{ route('remote.dashboard') }}" class="inline-flex w-fit items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali ke dashboard</a>
    </div>
</header>

@if (session('status'))
    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-900" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-base leading-7 text-rose-900" role="alert">
        <p class="font-bold">Data belum tersimpan. Periksa kembali:</p>
        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

<dialog id="create-school-modal" data-open-on-load="{{ old('form_context') === 'school' ? 'true' : 'false' }}" class="fixed inset-0 m-auto max-h-[92dvh] w-[calc(100%-1.5rem)] max-w-xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/55 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-7">
        <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div><p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Institusi</p><h2 id="new-school-title" class="mt-1 text-2xl font-bold text-slate-950">Tambah sekolah</h2><p class="mt-1 text-sm leading-6 text-slate-600">Buat satu entri untuk setiap sekolah yang memakai portal.</p></div>
            <button type="button" data-close-school-modal class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-2xl leading-none text-slate-500 hover:bg-slate-100" aria-label="Tutup modal">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.schools.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="form_context" value="school">
            <label class="block text-sm font-semibold text-slate-800">Nama sekolah<input name="name" value="{{ old('form_context') === 'school' ? old('name') : '' }}" required maxlength="160" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600" placeholder="SMP Harapan Bangsa"></label>
            <label class="block text-sm font-semibold text-slate-800">Kode unik<input name="code" value="{{ old('form_context') === 'school' ? old('code') : '' }}" required maxlength="40" pattern="[A-Za-z0-9_-]+" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600" placeholder="SMP-HARAPAN"></label>
            <label class="block text-sm font-semibold text-slate-800">Alamat <span class="font-normal text-slate-500">(opsional)</span><input name="address" value="{{ old('form_context') === 'school' ? old('address') : '' }}" maxlength="500" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600" placeholder="Kota atau alamat sekolah"></label>
            <p class="rounded-xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-600">Setelah sekolah dibuat, tambahkan rombel sebelum membuat akun guru dan siswa.</p>
            <div class="flex justify-end border-t border-slate-100 pt-4"><button class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tambah sekolah</button></div>
        </form>
    </div>
</dialog>

<section class="mt-6 space-y-5" aria-label="Daftar sekolah">
    @forelse ($schools as $school)
        <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 p-5 sm:p-6">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.1em] text-indigo-700">{{ $school->code }}</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-950">{{ $school->name }}</h2>
                    @if ($school->address)<p class="mt-1 text-sm text-slate-600">{{ $school->address }}</p>@endif
                </div>
                <div class="flex flex-wrap gap-2 text-sm font-medium text-slate-700">
                    <span class="rounded-lg bg-orange-50 px-3 py-2">{{ number_format($school->student_count, 0, ',', '.') }} siswa</span>
                    <span class="rounded-lg bg-indigo-50 px-3 py-2">{{ number_format($school->classrooms->count(), 0, ',', '.') }} rombel</span>
                </div>
            </div>

            <div class="grid gap-5 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.8fr)]">
                <div>
                    <h3 class="font-bold text-slate-950">Rombongan belajar</h3>
                    @if ($school->classrooms->isEmpty())
                        <p class="mt-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">Belum ada rombel untuk sekolah ini.</p>
                    @else
                        <div class="mt-3 divide-y divide-slate-100 rounded-xl border border-slate-100 px-4">
                            @foreach ($school->classrooms as $classroom)
                                <div class="flex flex-wrap items-center justify-between gap-3 py-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $classroom->name }} <span class="font-normal text-slate-500">· {{ $classroom->academic_year }}</span></p>
                                        <p class="mt-0.5 text-sm text-slate-600">{{ $classroom->level ?: 'Tingkat belum diisi' }} · {{ $classroom->students_count }} siswa · {{ $classroom->subjects_count }} mapel</p>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('admin.classrooms.students', $classroom) }}" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kelola siswa</a>
                                        <button type="button" data-open-edit-classroom-modal data-edit-action="{{ route('admin.classrooms.update', $classroom) }}" data-classroom-id="{{ $classroom->id }}" data-classroom-name="{{ $classroom->name }}" data-classroom-level="{{ $classroom->level }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">Edit</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex h-fit flex-col items-start justify-between gap-3 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 sm:flex-row sm:items-center lg:flex-col lg:items-start">
                    <div><h3 class="font-bold text-slate-950">Kelola rombel</h3><p class="mt-1 text-sm leading-6 text-slate-600">Tambahkan rombel baru untuk sekolah ini.</p></div>
                    <button type="button" data-open-classroom-modal data-create-action="{{ route('admin.schools.classrooms.store', $school) }}" data-school-id="{{ $school->id }}" data-school-name="{{ $school->name }}" class="shrink-0 rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tambah rombel</button>
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <h2 class="font-bold text-slate-950">Belum ada sekolah terdaftar</h2>
            <p class="mt-1 text-sm text-slate-600">Klik tombol Tambah sekolah untuk mendaftarkan sekolah pertama.</p>
        </div>
    @endforelse
</section>

@php($schoolForClassroomModal = $schools->firstWhere('id', old('school_id')))
@php($classroomForEditModal = $schools->pluck('classrooms')->flatten()->firstWhere('id', old('classroom_id')))

<dialog id="create-classroom-modal" data-open-on-load="{{ old('form_context') === 'classroom' ? 'true' : 'false' }}" data-default-action="{{ $schoolForClassroomModal ? route('admin.schools.classrooms.store', $schoolForClassroomModal) : '' }}" class="fixed inset-0 m-auto max-h-[92dvh] w-[calc(100%-1.5rem)] max-w-xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/55 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-7">
        <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div><p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Rombongan belajar</p><h2 class="mt-1 text-2xl font-bold text-slate-950">Tambah rombel</h2><p class="mt-1 text-sm text-slate-600">Untuk sekolah <span data-create-classroom-school-name>{{ $schoolForClassroomModal?->name }}</span></p></div>
            <button type="button" data-close-classroom-modal class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-2xl leading-none text-slate-500 hover:bg-slate-100" aria-label="Tutup modal">&times;</button>
        </div>
        <form id="create-classroom-form" method="POST" action="{{ $schoolForClassroomModal ? route('admin.schools.classrooms.store', $schoolForClassroomModal) : '' }}" class="space-y-4">
            @csrf
            <input type="hidden" name="form_context" value="classroom">
            <input id="create-classroom-school-id" type="hidden" name="school_id" value="{{ old('form_context') === 'classroom' ? old('school_id') : '' }}">
            <label class="block text-sm font-semibold text-slate-800">Nama rombel<input name="name" value="{{ old('form_context') === 'classroom' ? old('name') : '' }}" required maxlength="100" placeholder="8A" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600"></label>
            <label class="block text-sm font-semibold text-slate-800">Tahun ajaran<input name="academic_year" value="{{ old('form_context') === 'classroom' ? old('academic_year') : '' }}" required maxlength="20" placeholder="2026/2027" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600"></label>
            <label class="block text-sm font-semibold text-slate-800">Tingkat <span class="font-normal text-slate-500">(opsional)</span><input name="level" value="{{ old('form_context') === 'classroom' ? old('level') : '' }}" maxlength="40" placeholder="Kelas 8" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600"></label>
            <div class="flex justify-end border-t border-slate-100 pt-4"><button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tambah rombel</button></div>
        </form>
    </div>
</dialog>

<dialog id="edit-classroom-modal" data-open-on-load="{{ old('form_context') === 'edit_classroom' ? 'true' : 'false' }}" data-default-action="{{ $classroomForEditModal ? route('admin.classrooms.update', $classroomForEditModal) : '' }}" class="fixed inset-0 m-auto max-h-[92dvh] w-[calc(100%-1.5rem)] max-w-xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/55 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-7">
        <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div><p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Rombongan belajar</p><h2 class="mt-1 text-2xl font-bold text-slate-950">Edit rombel</h2><p class="mt-1 text-sm text-slate-600">Ubah nama atau tingkat. Tahun ajaran tetap dikunci.</p></div>
            <button type="button" data-close-edit-classroom-modal class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-2xl leading-none text-slate-500 hover:bg-slate-100" aria-label="Tutup modal">&times;</button>
        </div>
        <form id="edit-classroom-form" method="POST" action="{{ $classroomForEditModal ? route('admin.classrooms.update', $classroomForEditModal) : '' }}" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="form_context" value="edit_classroom">
            <input id="edit-classroom-id" type="hidden" name="classroom_id" value="{{ old('form_context') === 'edit_classroom' ? old('classroom_id') : '' }}">
            <label class="block text-sm font-semibold text-slate-800">Nama rombel<input id="edit-classroom-name" name="name" value="{{ old('form_context') === 'edit_classroom' ? old('name') : '' }}" required maxlength="100" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600"></label>
            <label class="block text-sm font-semibold text-slate-800">Tingkat <span class="font-normal text-slate-500">(opsional)</span><input id="edit-classroom-level" name="level" value="{{ old('form_context') === 'edit_classroom' ? old('level') : '' }}" maxlength="40" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600"></label>
            <div class="flex justify-end border-t border-slate-100 pt-4"><button type="submit" class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Simpan perubahan</button></div>
        </form>
    </div>
</dialog>
@endsection
