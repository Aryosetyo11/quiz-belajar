@extends('layouts.portal')

@section('title', 'Penugasan Mata Pelajaran')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Pengelolaan akademik</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Penugasan mapel</h1>
        <p class="mt-2 max-w-3xl text-base leading-7 text-slate-600">Hubungkan sekolah, rombel, mata pelajaran, dan guru. Siswa di rombel akan mendapat akses ke seluruh mapel yang ditugaskan ke kelasnya.</p>
    </div>
    <div class="flex gap-4"><a href="{{ route('admin.schools') }}" class="text-sm font-bold text-teal-800 underline-offset-4 hover:underline">Kelola sekolah & rombel</a><a href="{{ route('remote.dashboard') }}" class="text-sm font-bold text-teal-800 underline-offset-4 hover:underline">← Dashboard</a></div>
</div>

@if ($errors->any())
    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-base leading-7 text-rose-900" role="alert">
        <p class="font-bold">Perubahan belum disimpan. Periksa data berikut:</p>
        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="new-subject-title">
    <div class="flex items-start gap-4">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-lg font-bold text-teal-800">+</span>
        <div>
            <h2 id="new-subject-title" class="text-xl font-bold text-slate-950">Buat mata pelajaran dan tugaskan guru</h2>
            <p class="mt-1 text-base leading-7 text-slate-600">Pilih sekolah dan rombel terlebih dahulu, kemudian tentukan mata pelajaran dan guru pengampunya.</p>
        </div>
    </div>

    @if ($teachers->isEmpty() || $schools->isEmpty())
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-base leading-7 text-amber-950">
            @if ($schools->isEmpty())
                Belum ada sekolah aktif. <a href="{{ route('admin.schools') }}" class="font-bold underline underline-offset-2">Buat sekolah dan rombel terlebih dahulu</a>.
            @else
                Belum ada akun guru. <a href="{{ route('admin.users') }}#buat-akun" class="font-bold underline underline-offset-2">Buat akun guru terlebih dahulu</a>.
            @endif
        </div>
    @else
        <form method="POST" action="{{ route('admin.assignments.store') }}" class="mt-5 grid gap-5 lg:grid-cols-2" data-academic-assignment>
            @csrf
            <label for="assignment-school" class="block text-base font-semibold text-slate-800">Sekolah
                <select id="assignment-school" name="school_id" data-assignment-school required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-base focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Pilih sekolah</option>
                    @foreach ($schools as $school)<option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>@endforeach
                </select>
            </label>
            <label class="block text-base font-semibold text-slate-800">Rombel
                <select name="classroom_id" data-assignment-classroom required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-base focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Pilih rombel</option>
                    @foreach ($schools as $school)@foreach ($school->classrooms as $classroom)<option value="{{ $classroom->id }}" data-school-id="{{ $school->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $school->name }} · {{ $classroom->name }} ({{ $classroom->academic_year }})</option>@endforeach @endforeach
                </select>
            </label>
            <label for="assignment-teacher" class="block text-base font-semibold text-slate-800">Guru pengampu
                <select id="assignment-teacher" name="teacher_id" data-assignment-teacher required class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-base focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Pilih guru</option>
                    @foreach ($teachers as $teacher)
                        <option value="{{ $teacher->id }}" data-school-id="{{ $teacher->school_id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }} — {{ $teacher->school->name ?? 'Sekolah belum dipilih' }}</option>
                    @endforeach
                </select>
            </label>
            <label for="assignment-subject-name" class="block text-base font-semibold text-slate-800">Nama mata pelajaran
                <input id="assignment-subject-name" name="name" value="{{ old('name') }}" required maxlength="120" placeholder="Contoh: Matematika Kelas 8A" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-base focus:border-teal-600 focus:ring-teal-600" />
            </label>
            <label for="assignment-subject-description" class="block text-base font-semibold text-slate-800 lg:col-span-2">Keterangan kelas <span class="font-normal text-slate-500">(opsional)</span>
                <textarea id="assignment-subject-description" name="description" rows="3" maxlength="1000" placeholder="Tingkat kelas atau catatan singkat untuk pengajar" class="mt-2 w-full rounded-xl border-slate-300 px-4 py-3 text-base leading-7 focus:border-teal-600 focus:ring-teal-600">{{ old('description') }}</textarea>
            </label>
            <div class="flex flex-col gap-3 border-t border-slate-100 pt-5 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm leading-6 text-slate-600">Setelah ditugaskan, kelas muncul pada portal guru tersebut.</p>
                <button type="submit" class="inline-flex justify-center rounded-xl bg-teal-800 px-5 py-3 text-base font-bold text-white transition hover:bg-teal-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-200">Buat penugasan</button>
            </div>
        </form>
    @endif
</section>

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="assignment-list-title">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.1em] text-violet-700">Daftar kelas</p>
            <h2 id="assignment-list-title" class="mt-1 text-xl font-bold text-slate-950">Mata pelajaran dan pengampu</h2>
        </div>
        <span class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700">{{ number_format($subjects->count(), 0, ',', '.') }} mata pelajaran</span>
    </div>

    @if ($subjects->isEmpty())
        <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
            <p class="font-semibold text-slate-900">Belum ada mata pelajaran</p>
            <p class="mt-1 text-sm leading-6 text-slate-600">Gunakan form di atas untuk membuat kelas pertama dan menentukan pengajarnya.</p>
        </div>
    @else
        <div class="mt-5 space-y-4">
            @foreach ($subjects as $subject)
                <article class="rounded-2xl border border-slate-200 p-4 sm:p-5">
                    <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-slate-950">{{ $subject->name }}</h3>
                            <p class="mt-1 text-base text-slate-600">{{ $subject->school->name }} · {{ $subject->classroom->name }} · Pengampu: <strong class="text-slate-800">{{ $subject->teacher->name }}</strong></p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-sm font-medium text-slate-600">
                            <span class="rounded-lg bg-orange-50 px-3 py-1.5 text-orange-900">{{ $subject->students_count }} siswa</span>
                            <span class="rounded-lg bg-indigo-50 px-3 py-1.5 text-indigo-900">{{ $subject->materials_count }} materi</span>
                            <span class="rounded-lg bg-violet-50 px-3 py-1.5 text-violet-900">{{ $subject->sessions_count }} sesi ujian</span>
                        </div>
                    </div>

                    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-teal-50 px-4 py-3">
                        <p class="text-sm leading-6 text-teal-950">Atur siswa yang mendapat akses ke materi dan ujian kelas ini.</p>
                        <a href="{{ route('admin.classrooms.students', $subject->classroom) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-teal-800 px-4 py-2 text-sm font-bold text-white transition hover:bg-teal-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-200">Kelola rombel</a>
                    </div>

                    <details class="group rounded-xl bg-slate-50">
                        <summary class="cursor-pointer list-none px-4 py-3 text-sm font-bold text-teal-800 marker:hidden">Edit nama atau pindahkan pengampu</summary>
                        <form method="POST" action="{{ route('admin.assignments.update', $subject) }}" data-academic-assignment data-confirm="Perubahan ini akan memperbarui sekolah, rombel, dan pengampu mapel." data-confirm-title="Simpan perubahan penugasan?" data-confirm-btn="Ya, simpan" class="grid gap-4 border-t border-slate-200 p-4 lg:grid-cols-2">
                            @csrf
                            @method('PUT')
                            <label class="block text-sm font-semibold text-slate-700">Sekolah
                                <select name="school_id" data-assignment-school required class="mt-1.5 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base focus:border-teal-600 focus:ring-teal-600">
                                    @foreach ($schools as $school)<option value="{{ $school->id }}" @selected((int) $subject->school_id === (int) $school->id)>{{ $school->name }}</option>@endforeach
                                </select>
                            </label>
                            <label class="block text-sm font-semibold text-slate-700">Rombel
                                <select name="classroom_id" data-assignment-classroom required class="mt-1.5 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base focus:border-teal-600 focus:ring-teal-600">
                                    @foreach ($schools as $school)@foreach ($school->classrooms as $classroom)<option value="{{ $classroom->id }}" data-school-id="{{ $school->id }}" @selected((int) $subject->classroom_id === (int) $classroom->id)>{{ $school->name }} · {{ $classroom->name }} ({{ $classroom->academic_year }})</option>@endforeach @endforeach
                                </select>
                            </label>
                            <label class="block text-sm font-semibold text-slate-700">Guru pengampu
                                <select name="teacher_id" data-assignment-teacher required class="mt-1.5 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base focus:border-teal-600 focus:ring-teal-600">
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" data-school-id="{{ $teacher->school_id }}" @selected((int) $subject->teacher_id === (int) $teacher->id)>{{ $teacher->name }} — {{ $teacher->school->name ?? 'Sekolah belum dipilih' }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block text-sm font-semibold text-slate-700">Nama mata pelajaran
                                <input name="name" value="{{ $subject->name }}" required maxlength="120" class="mt-1.5 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base focus:border-teal-600 focus:ring-teal-600" />
                            </label>
                            <label class="block text-sm font-semibold text-slate-700 lg:col-span-2">Keterangan
                                <textarea name="description" rows="2" maxlength="1000" class="mt-1.5 w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-base leading-6 focus:border-teal-600 focus:ring-teal-600">{{ $subject->description }}</textarea>
                            </label>
                            <div class="flex flex-col gap-3 border-t border-slate-200 pt-4 lg:col-span-2 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-sm leading-6 text-slate-600">Sekolah guru, rombel, dan mapel harus selaras. Siswa kelas otomatis terhubung ke mapel.</p>
                                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">Simpan perubahan</button>
                            </div>
                        </form>
                    </details>

                    <details class="mt-3 rounded-xl border border-slate-200">
                        <summary class="cursor-pointer px-4 py-3 text-sm font-bold text-slate-800">Jadwal belajar · {{ $subject->schedules->count() }} jadwal</summary>
                        <div class="border-t border-slate-100 p-4">
                            @if ($subject->schedules->isNotEmpty())
                                <ul class="mb-4 divide-y divide-slate-100 rounded-xl border border-slate-100 px-4">
                                    @foreach ($subject->schedules as $schedule)
                                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                                            <p class="text-sm font-semibold text-slate-900">{{ [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'][$schedule->day_of_week] }} · {{ substr($schedule->starts_at, 0, 5) }}–{{ substr($schedule->ends_at, 0, 5) }} @if ($schedule->location)<span class="font-normal text-slate-600">· {{ $schedule->location }}</span>@endif</p>
                                            <form method="POST" action="{{ route('admin.assignments.schedules.destroy', [$subject, $schedule]) }}" data-confirm="Hapus jadwal ini?" data-confirm-title="Hapus jadwal mapel?" data-confirm-btn="Hapus" data-confirm-variant="danger">
                                                @csrf @method('DELETE')
                                                <button class="rounded-lg px-3 py-1.5 text-sm font-semibold text-rose-700 hover:bg-rose-50">Hapus</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mb-4 text-sm text-slate-600">Jadwal belum ditentukan.</p>
                            @endif
                            <form method="POST" action="{{ route('admin.assignments.schedules.store', $subject) }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                                @csrf
                                <label class="text-sm font-semibold text-slate-700">Hari
                                    <select name="day_of_week" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600">
                                        @foreach ([1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'] as $day => $dayName)<option value="{{ $day }}">{{ $dayName }}</option>@endforeach
                                    </select>
                                </label>
                                <label class="text-sm font-semibold text-slate-700">Mulai<input name="starts_at" type="time" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
                                <label class="text-sm font-semibold text-slate-700">Selesai<input name="ends_at" type="time" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
                                <label class="text-sm font-semibold text-slate-700">Ruangan <span class="font-normal text-slate-500">(opsional)</span><input name="location" maxlength="120" placeholder="Ruang 8A" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"></label>
                                <button class="self-end rounded-lg bg-indigo-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-800">Tambah jadwal</button>
                            </form>
                        </div>
                    </details>
                </article>
            @endforeach
        </div>
    @endif
</section>

<script>
    document.querySelectorAll('[data-academic-assignment]').forEach((form) => {
        const schoolSelect = form.querySelector('[data-assignment-school]');
        const classroomSelect = form.querySelector('[data-assignment-classroom]');
        const teacherSelect = form.querySelector('[data-assignment-teacher]');

        const filterSchoolOptions = () => {
            [classroomSelect, teacherSelect].forEach((select) => {
                if (!select) return;
                const previousValue = select.value;
                Array.from(select.options).forEach((option) => {
                    if (!option.dataset.schoolId) return;
                    option.hidden = option.dataset.schoolId !== schoolSelect.value;
                    option.disabled = option.hidden;
                });
                if (previousValue && select.selectedOptions[0]?.disabled) select.value = '';
            });
        };

        schoolSelect?.addEventListener('change', filterSchoolOptions);
        filterSchoolOptions();
    });
</script>
@endsection
