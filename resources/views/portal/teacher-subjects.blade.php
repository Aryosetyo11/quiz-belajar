@extends('layouts.portal')

@section('title', 'Mata Pelajaran | RuangUji')

@section('content')
    <header>
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Pengelolaan pembelajaran</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Mata Pelajaran</h1>
        <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Buat mata pelajaran dan daftarkan siswa dengan NISN. Setiap mata pelajaran hanya terlihat oleh pengajar pemilik dan siswa yang terdaftar.</p>
    </header>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800" role="status">{{ session('status') }}</div>
    @endif

    <div class="mt-8 grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <section aria-labelledby="subjects-heading">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="subjects-heading" class="text-xl font-semibold text-slate-950">Mata pelajaran saya</h2>
                    <p class="mt-1 text-base text-slate-600">{{ $subjects->count() }} mata pelajaran</p>
                </div>
            </div>

            @forelse ($subjects as $subject)
                <article class="mb-4 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold text-slate-950">{{ $subject->name }}</h3>
                            <p class="mt-1 text-base leading-7 text-slate-600">{{ $subject->description ?: 'Belum ada deskripsi.' }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-700">{{ $subject->students_count }} siswa</span>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('teacher.subjects.materials', $subject) }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Kelola materi
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $subject->materials_count }}</span>
                        </a>
                    </div>

                    <details class="mt-5 border-t border-slate-100 pt-4">
                        <summary class="cursor-pointer text-base font-semibold text-slate-800">Kelola siswa</summary>
                        <div class="mt-4">
                            @php($nisnField = 'students.'.$subject->id.'.nisn')
                            <form method="POST" action="{{ route('teacher.subjects.students.store', $subject) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                                @csrf
                                <div class="flex-1">
                                    <label for="nisn-{{ $subject->id }}" class="mb-2 block text-base font-medium text-slate-800">Tambahkan dengan NISN</label>
                                    <input id="nisn-{{ $subject->id }}" name="students[{{ $subject->id }}][nisn]" type="text" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" value="{{ old($nisnField) }}" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100" placeholder="10 digit NISN" aria-describedby="nisn-error-{{ $subject->id }}">
                                    @error($nisnField)
                                        <p id="nisn-error-{{ $subject->id }}" class="mt-2 text-base text-red-700" role="alert">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-base font-semibold text-white transition hover:bg-slate-800">Tambahkan siswa</button>
                            </form>

                            <div class="mt-5">
                                <h4 class="text-base font-semibold text-slate-900">Siswa terdaftar</h4>
                                @if ($subject->students->isEmpty())
                                    <p class="mt-2 text-base text-slate-600">Belum ada siswa di mata pelajaran ini.</p>
                                @else
                                    <ul class="mt-2 divide-y divide-slate-100">
                                        @foreach ($subject->students as $student)
                                            <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                                <span class="min-w-0">
                                                    <span class="block truncate text-base font-medium text-slate-800">{{ $student->name }}</span>
                                                    <span class="mt-0.5 block text-sm text-slate-600">NISN {{ $student->nisn }}</span>
                                                </span>
                                                <form method="POST" action="{{ route('teacher.subjects.students.destroy', [$subject, $student]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-md px-3 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">Keluarkan</button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </details>

                    <details class="mt-4 border-t border-slate-100 pt-4">
                        <summary class="cursor-pointer text-base font-semibold text-slate-800">Edit informasi</summary>
                        @php($subjectInput = 'subjects.'.$subject->id)
                        <form method="POST" action="{{ route('teacher.subjects.update', $subject) }}" class="mt-4 space-y-4">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="name-{{ $subject->id }}" class="mb-2 block text-base font-medium text-slate-800">Nama mata pelajaran</label>
                                <input id="name-{{ $subject->id }}" name="subjects[{{ $subject->id }}][name]" type="text" value="{{ old($subjectInput.'.name', $subject->name) }}" required maxlength="120" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">
                                @error($subjectInput.'.name')
                                    <p class="mt-2 text-base text-red-700" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="description-{{ $subject->id }}" class="mb-2 block text-base font-medium text-slate-800">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label>
                                <textarea id="description-{{ $subject->id }}" name="subjects[{{ $subject->id }}][description]" rows="3" maxlength="1000" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100">{{ old($subjectInput.'.description', $subject->description) }}</textarea>
                                @error($subjectInput.'.description')
                                    <p class="mt-2 text-base text-red-700" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2.5 text-base font-semibold text-slate-700 transition hover:bg-slate-50">Simpan perubahan</button>
                        </form>
                    </details>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center">
                    <h3 class="text-lg font-semibold text-slate-950">Belum ada mata pelajaran</h3>
                    <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Buat mata pelajaran dari formulir di samping. Setelah dibuat, Anda bisa mendaftarkan siswa menggunakan NISN mereka.</p>
                </div>
            @endforelse
        </section>

        <aside class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="create-subject-heading">
            <h2 id="create-subject-heading" class="text-xl font-semibold text-slate-950">Buat mata pelajaran</h2>
            <p class="mt-2 text-base leading-7 text-slate-600">Mata pelajaran ini hanya dikelola oleh akun pengajar Anda.</p>

            <form method="POST" action="{{ route('teacher.subjects.store') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="new-name" class="mb-2 block text-base font-medium text-slate-800">Nama mata pelajaran</label>
                    <input id="new-name" name="name" type="text" value="{{ old('name') }}" required maxlength="120" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100" placeholder="Contoh: Matematika">
                    @error('name')
                        <p class="mt-2 text-base text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="new-description" class="mb-2 block text-base font-medium text-slate-800">Deskripsi <span class="font-normal text-slate-500">(opsional)</span></label>
                    <textarea id="new-description" name="description" rows="4" maxlength="1000" class="block w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100" placeholder="Jelaskan mata pelajaran secara singkat">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-base text-red-700" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-3 text-base font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Buat mata pelajaran</button>
            </form>
        </aside>
    </div>
@endsection
