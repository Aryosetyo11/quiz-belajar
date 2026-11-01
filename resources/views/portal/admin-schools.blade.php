<div>
    <!-- Because you are alive, everything is possible. - Thich Nhat Hanh -->
</div>
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
    <a href="{{ route('remote.dashboard') }}" class="inline-flex w-fit items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali ke dashboard</a>
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

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="new-school-title">
    <h2 id="new-school-title" class="text-xl font-bold text-slate-950">Tambah sekolah</h2>
    <p class="mt-1 text-sm leading-6 text-slate-600">Buat satu entri untuk setiap sekolah yang memakai portal ini.</p>
    <form method="POST" action="{{ route('admin.schools.store') }}" class="mt-5 grid gap-4 md:grid-cols-3">
        @csrf
        <label class="text-sm font-semibold text-slate-800">Nama sekolah
            <input name="name" value="{{ old('name') }}" required maxlength="160" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600" placeholder="SMP Harapan Bangsa">
        </label>
        <label class="text-sm font-semibold text-slate-800">Kode unik
            <input name="code" value="{{ old('code') }}" required maxlength="40" pattern="[A-Za-z0-9_-]+" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600" placeholder="SMP-HARAPAN">
        </label>
        <label class="text-sm font-semibold text-slate-800">Alamat <span class="font-normal text-slate-500">(opsional)</span>
            <input name="address" value="{{ old('address') }}" maxlength="500" class="mt-1.5 w-full rounded-lg border-slate-300 px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600" placeholder="Kota atau alamat sekolah">
        </label>
        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 md:col-span-3">
            <p class="text-sm text-slate-600">Setelah sekolah dibuat, tambahkan rombel sebelum membuat akun guru dan siswa.</p>
            <button class="rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tambah sekolah</button>
        </div>
    </form>
</section>

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
                                        <details class="relative">
                                            <summary class="cursor-pointer list-none rounded-lg px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Edit</summary>
                                            <form method="POST" action="{{ route('admin.classrooms.update', $classroom) }}" class="absolute right-0 z-10 mt-1 w-72 rounded-xl border border-slate-200 bg-white p-4 shadow-lg">
                                                @csrf @method('PUT')
                                                <label class="block text-sm font-semibold text-slate-800">Nama rombel<input name="name" value="{{ $classroom->name }}" required maxlength="100" class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm"></label>
                                                <label class="mt-3 block text-sm font-semibold text-slate-800">Tingkat<input name="level" value="{{ $classroom->level }}" maxlength="40" class="mt-1 w-full rounded-lg border-slate-300 px-3 py-2 text-sm"></label>
                                                <p class="mt-2 text-xs leading-5 text-slate-500">Tahun ajaran dikunci agar riwayat kelas tetap konsisten.</p>
                                                <button class="mt-3 w-full rounded-lg bg-indigo-700 px-3 py-2 text-sm font-semibold text-white">Simpan</button>
                                            </form>
                                        </details>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.schools.classrooms.store', $school) }}" class="h-fit rounded-xl bg-slate-50 p-4">
                    @csrf
                    <h3 class="font-bold text-slate-950">Tambah rombel</h3>
                    <div class="mt-3 space-y-3">
                        <label class="block text-sm font-semibold text-slate-800">Nama rombel
                            <input name="name" required maxlength="100" placeholder="8A" class="mt-1 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600">
                        </label>
                        <label class="block text-sm font-semibold text-slate-800">Tahun ajaran
                            <input name="academic_year" required maxlength="20" placeholder="2026/2027" class="mt-1 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600">
                        </label>
                        <label class="block text-sm font-semibold text-slate-800">Tingkat <span class="font-normal text-slate-500">(opsional)</span>
                            <input name="level" maxlength="40" placeholder="Kelas 8" class="mt-1 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base focus:border-indigo-600 focus:ring-indigo-600">
                        </label>
                        <button class="w-full rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tambah rombel</button>
                    </div>
                </form>
            </div>
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <h2 class="font-bold text-slate-950">Belum ada sekolah terdaftar</h2>
            <p class="mt-1 text-sm text-slate-600">Tambahkan sekolah pertama melalui formulir di atas.</p>
        </div>
    @endforelse
</section>
@endsection
