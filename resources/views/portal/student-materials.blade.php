@extends('layouts.portal')

@section('title', 'Materi '.$subject->name.' | RuangUji')

@section('content')
    <a href="{{ route('student.subjects') }}" class="text-base font-medium text-slate-600 underline-offset-4 hover:text-slate-900 hover:underline">← Kembali ke mata pelajaran</a>
    <header class="mt-5">
        <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Pembelajaran saya</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $subject->name }}</h1>
        <p class="mt-2 text-base leading-7 text-slate-600">Pengajar: {{ $subject->teacher->name }}</p>
    </header>

    <section class="mt-8 rounded-xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="student-materials-title">
        <div class="flex items-baseline justify-between gap-3">
            <h2 id="student-materials-title" class="text-xl font-semibold text-slate-950">Materi pembelajaran</h2>
            <span class="text-sm text-slate-500">{{ $materials->count() }} file</span>
        </div>

        @if ($materials->isEmpty())
            <div class="mt-5 rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center">
                <h3 class="text-lg font-semibold text-slate-900">Materi belum ditambahkan</h3>
                <p class="mt-2 text-base leading-7 text-slate-600">Pengajar belum mengunggah materi untuk mata pelajaran ini. Coba cek kembali nanti.</p>
            </div>
        @else
            <ul class="mt-4 divide-y divide-slate-100">
                @foreach ($materials as $material)
                    <li class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-semibold text-slate-900">{{ $material->title }}</h3>
                            <p class="mt-1 text-sm text-slate-600">{{ $material->original_file_name }} · {{ number_format($material->file_size / 1024, 0) }} KB · Diunggah {{ $material->created_at->format('d M Y') }}</p>
                            @if ($material->description)
                                <p class="mt-2 text-base leading-6 text-slate-600">{{ $material->description }}</p>
                            @endif
                        </div>
                        <a href="{{ route('subject-materials.download', [$subject, $material]) }}" class="inline-flex w-fit shrink-0 items-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Unduh materi</a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
