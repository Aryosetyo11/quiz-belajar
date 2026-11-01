@extends('layouts.portal')

@section('title', 'Buat Sesi Ujian | RuangUji')

@section('content')
    <header>
        <a href="{{ route('teacher.exam-sessions') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Kembali ke sesi ujian</a>
        <p class="mt-5 text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Evaluasi pembelajaran</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Buat sesi ujian</h1>
        <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">Pilih mapel dan paket soalnya, lalu tentukan jumlah soal. Satu sesi terhubung ke satu mapel agar jadwal dan hasilnya tercatat dengan benar.</p>
    </header>

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">{{ $errors->first() }}</div>
    @endif

    @if ($bankPackages->isEmpty())
        <section class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
            <h2 class="text-xl font-semibold text-slate-950">Bank Soal masih kosong</h2>
            <p class="mx-auto mt-2 max-w-xl text-base leading-7 text-slate-600">Buat paket dan tambahkan soal terlebih dahulu. Setelah itu, paket tersebut bisa dipakai berulang kali untuk sesi ujian.</p>
            <a href="{{ route('teacher.question-bank') }}" class="mt-5 inline-flex rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-800">Buka Bank Soal</a>
        </section>
    @else
        <form method="POST" action="{{ route('teacher.exam-sessions.store') }}" class="mt-8 space-y-6">
            @csrf
            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-semibold text-slate-950">Sumber soal dan jumlah</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Jika mengampu beberapa mapel, buat satu sesi untuk masing-masing mapel. Jumlah esai akan sama untuk semua peserta dalam sesi tersebut.</p>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="exam-subject" class="mb-2 block text-sm font-semibold text-slate-800">Mata pelajaran</label>
                        <select id="exam-subject" name="subject_id" required data-exam-subject-select class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base text-slate-900">
                            <option value="">Pilih mata pelajaran</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected($selectedSubjectId === $subject->id)>
                                    {{ $subject->name }} · {{ $subject->school?->name ?? 'Sekolah' }} / {{ $subject->classroom?->name ?? 'Rombel' }} ({{ $subject->classroom?->academic_year ?? 'Tahun ajaran' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="bank-package" class="mb-2 block text-sm font-semibold text-slate-800">Paket Bank Soal</label>
                        <select id="bank-package" name="bank_package_id" required data-exam-package-select data-old-package="{{ old('bank_package_id') }}" class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base text-slate-900" @disabled($selectedSubjectId === null)>
                            <option value="">{{ $selectedSubjectId === null ? 'Pilih mapel terlebih dahulu' : 'Pilih paket soal' }}</option>
                            @foreach ($bankPackages as $package)
                                <option value="{{ $package->id }}" data-subject-id="{{ $package->subject_id }}" @selected(old('bank_package_id') == $package->id && $selectedSubjectId === $package->subject_id) @disabled($selectedSubjectId !== null && $selectedSubjectId !== $package->subject_id) @if ($selectedSubjectId !== null && $selectedSubjectId !== $package->subject_id) hidden @endif>
                                    {{ $package->title }} ({{ $package->questions_count }} soal: {{ $package->multiple_choice_questions_count }} PG, {{ $package->essay_questions_count }} esai)
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-sm text-slate-500">Paket hanya menampilkan soal dari mapel yang dipilih.</p>
                    </div>
                    <div>
                        <label for="question-count" class="mb-2 block text-sm font-semibold text-slate-800">Jumlah soal yang diujikan</label>
                        <input id="question-count" type="number" name="question_count" value="{{ old('question_count', 20) }}" min="1" max="100" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base text-slate-900">
                    </div>
                    <div>
                        <label for="essay-count" class="mb-2 block text-sm font-semibold text-slate-800">Jumlah soal esai</label>
                        <input id="essay-count" type="number" name="essay_question_count" value="{{ old('essay_question_count', 0) }}" min="0" max="100" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base text-slate-900">
                    </div>
                </div>
                <div class="mt-5 rounded-xl bg-indigo-50 px-4 py-3 text-sm leading-6 text-indigo-950">
                    Contoh: isi <strong>20 soal</strong> dan <strong>3 esai</strong>. Sistem memilih 17 PG dan 3 esai secara acak untuk setiap siswa.
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7">
                <h2 class="text-xl font-semibold text-slate-950">Pengaturan ujian</h2>
                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="exam-title" class="mb-2 block text-sm font-semibold text-slate-800">Nama sesi ujian</label>
                        <input id="exam-title" name="title" value="{{ old('title') }}" required maxlength="160" class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base" placeholder="Contoh: Ujian Tengah Semester">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="mb-2 block text-sm font-semibold text-slate-800">Petunjuk untuk siswa <span class="font-normal text-slate-500">(opsional)</span></label>
                        <textarea id="description" name="description" rows="3" maxlength="2000" class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label for="duration" class="mb-2 block text-sm font-semibold text-slate-800">Durasi (menit)</label>
                        <input id="duration" type="number" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" min="1" max="300" required class="block w-full rounded-lg border border-slate-300 px-3.5 py-3 text-base">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label for="starts-at" class="mb-2 block text-sm font-semibold text-slate-800">Mulai <span class="font-normal text-slate-500">(opsional)</span></label><input id="starts-at" type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-3 text-sm"></div>
                        <div><label for="ends-at" class="mb-2 block text-sm font-semibold text-slate-800">Berakhir <span class="font-normal text-slate-500">(opsional)</span></label><input id="ends-at" type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="block w-full rounded-lg border border-slate-300 px-3 py-3 text-sm"></div>
                    </div>
                    <div class="sm:col-span-2 rounded-xl border border-slate-200 p-4">
                        <label class="flex items-center gap-3 text-sm font-semibold text-slate-900"><input type="checkbox" name="enable_proctoring" value="1" @checked(old('enable_proctoring', true)) class="h-4 w-4 rounded border-slate-300 text-indigo-700">Aktifkan pemantauan anti-kecurangan</label>
                        <div class="mt-3 flex items-center gap-3"><label for="max-violations" class="text-sm text-slate-700">Batas pelanggaran</label><input id="max-violations" type="number" name="max_violations" value="{{ old('max_violations', 3) }}" min="1" max="10" class="w-20 rounded-lg border border-slate-300 px-2.5 py-1.5 text-sm"><span class="text-sm text-slate-500">sebelum jawaban dikumpulkan otomatis</span></div>
                    </div>
                    <label class="sm:col-span-2 flex items-center gap-3 rounded-xl bg-emerald-50 p-4 text-sm font-semibold text-emerald-950"><input type="checkbox" name="is_published" value="1" @checked(old('is_published')) class="h-4 w-4 rounded border-slate-300 text-indigo-700">Publikasikan sekarang agar siswa bisa mengerjakan</label>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="rounded-xl bg-indigo-700 px-5 py-3 text-base font-semibold text-white transition hover:bg-indigo-800">Simpan sesi ujian</button>
            </div>
        </form>
    @endif
@endsection
