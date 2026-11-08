<div>
    <!-- You must be the change you wish to see in the world. - Mahatma Gandhi -->
</div>
@extends('layouts.portal')

@section('title', 'Kelola Siswa — '.$classroom->name)

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <a href="{{ route('admin.schools') }}" class="text-sm font-semibold text-indigo-800 hover:underline">← Kembali ke sekolah & rombel</a>
        <p class="mt-4 text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Pengelolaan kelas</p>
        <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Rombel {{ $classroom->name }}</h1>
        <p class="mt-2 text-base leading-7 text-slate-600">{{ $classroom->school->name }} · Tahun ajaran {{ $classroom->academic_year }}. Siswa di rombel ini mendapat akses ke semua mapel yang ditugaskan ke kelasnya. Satu siswa dapat terdaftar di beberapa rombel.</p>
    </div>
    <span class="w-fit rounded-lg bg-orange-50 px-4 py-2 text-sm font-semibold text-orange-950">{{ number_format($roster->total(), 0, ',', '.') }} siswa terdaftar</span>
</div>

@if (session('status'))
    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-base leading-7 text-emerald-900" role="status">{{ session('status') }}</div>
@endif

@if ($errors->any())
    <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-base leading-7 text-rose-900" role="alert">
        <p class="font-bold">Siswa belum ditambahkan.</p>
        <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul>
    </div>
@endif

<div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.2fr)_minmax(20rem,0.8fr)]">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="roster-title">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-violet-700">Daftar kelas</p>
                <h2 id="roster-title" class="mt-1 text-xl font-bold text-slate-950">Siswa terdaftar</h2>
            </div>
            <span class="text-sm text-slate-600">{{ $roster->firstItem() ?? 0 }}–{{ $roster->lastItem() ?? 0 }} dari {{ $roster->total() }}</span>
        </div>

        @if ($roster->isEmpty())
            <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                <p class="font-semibold text-slate-900">Belum ada siswa di kelas ini</p>
                <p class="mt-1 text-sm leading-6 text-slate-600">Pilih siswa yang akan didaftarkan dari panel di sebelah.</p>
            </div>
        @else
            <div class="mt-5 divide-y divide-slate-100">
                @foreach ($roster as $student)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-4 first:pt-0">
                        <div>
                            <p class="font-semibold text-slate-950">{{ $student->name }}</p>
                            <p class="mt-1 text-sm text-slate-600">NISN {{ $student->nisn }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.classrooms.students.destroy', [$classroom, $student]) }}" data-confirm="{{ $student->name }} akan kehilangan akses ke materi dan ujian semua mapel di rombel ini. Riwayat ujian yang sudah tersimpan tetap ada." data-confirm-title="Keluarkan siswa dari rombel?" data-confirm-btn="Ya, keluarkan" data-confirm-variant="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="min-h-10 rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-800 transition hover:bg-rose-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-rose-100">Keluarkan</button>
                        </form>
                    </div>
                @endforeach
            </div>

            @if ($roster->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">{{ $roster->links() }}</div>
            @endif
        @endif
    </section>

    <section class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="add-student-title">
        <p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Pendaftaran</p>
        <h2 id="add-student-title" class="mt-1 text-xl font-bold text-slate-950">Masukkan siswa ke kelas</h2>
        <p class="mt-2 text-sm leading-6 text-slate-600">Pilih siswa dari sekolah yang sama. Siswa yang sudah terdaftar di rombel lain tetap dapat ditambahkan. Pendaftaran memberi akses ke mapel yang terhubung dengan rombel ini.</p>

        <form method="GET" action="{{ route('admin.classrooms.students', $classroom) }}" class="mt-5 flex gap-2">
            <label for="candidate-search" class="sr-only">Cari siswa berdasarkan nama atau NISN</label>
            <input id="candidate-search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Cari nama atau NISN" class="min-w-0 flex-1 rounded-lg border-slate-300 px-3 py-2.5 text-sm focus:border-teal-600 focus:ring-teal-600">
            <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Cari</button>
        </form>

        @if ($availableStudents->isEmpty())
            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                {{ request('q') ? 'Tidak ada siswa yang cocok dan belum terdaftar di rombel ini.' : 'Semua siswa sudah terdaftar atau belum ada akun siswa di sekolah ini.' }}
            </div>
        @else
            <div class="mt-4 divide-y divide-slate-100">
                @foreach ($availableStudents as $student)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $student->name }}</p>
                            <p class="mt-0.5 text-sm text-slate-600">NISN {{ $student->nisn }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.classrooms.students.store', $classroom) }}">
                            @csrf
                            <input type="hidden" name="student_id" value="{{ $student->id }}">
                            <button type="submit" class="min-h-10 rounded-lg bg-teal-800 px-3 py-2 text-sm font-bold text-white transition hover:bg-teal-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-200">Tambahkan</button>
                        </form>
                    </div>
                @endforeach
            </div>

            @if ($availableStudents->hasPages())
                <div class="mt-4 border-t border-slate-100 pt-4">{{ $availableStudents->links() }}</div>
            @endif
        @endif
    </section>
</div>
@endsection
