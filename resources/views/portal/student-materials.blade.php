@extends('layouts.portal')

@section('title', 'Materi '.$subject->name.' | RuangUji')

@section('content')
    <a href="{{ route('student.subjects') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-700">&larr; Kembali ke mata pelajaran</a>
    <header class="mt-5"><p class="text-sm font-bold uppercase tracking-[0.1em] text-indigo-700">Pembelajaran saya</p><h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $subject->name }}</h1><p class="mt-2 text-base text-slate-600">Pengajar: {{ $subject->teacher->name }}</p></header>
    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 sm:p-6" aria-labelledby="student-materials-title">
        <div class="flex items-center justify-between gap-3"><h2 id="student-materials-title" class="text-xl font-bold text-slate-950">Materi pembelajaran</h2><span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-800">{{ $materials->count() }} materi</span></div>
        @if ($materials->isEmpty())<div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-7 text-center"><h3 class="font-bold text-slate-900">Materi belum ditambahkan</h3><p class="mt-2 text-sm leading-6 text-slate-600">Pengajar belum menambahkan materi untuk mata pelajaran ini.</p></div>
        @else
            <ul class="mt-4 divide-y divide-slate-100">@foreach ($materials as $material)<li class="py-5 first:pt-2"><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-900">{{ $material->title }}</h3><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $material->isVideo() ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-700' }}">{{ $material->isVideo() ? 'Video' : 'Dokumen' }}</span></div>@if ($material->description)<p class="mt-2 text-sm leading-6 text-slate-600">{{ $material->description }}</p>@endif
                @if ($material->isVideo())
                    @if ($material->videoEmbedUrl())<div class="mt-4 w-full overflow-hidden rounded-xl border border-slate-200 bg-black" aria-label="Pemutar video {{ $material->title }}"><div class="aspect-video"><iframe src="{{ $material->videoEmbedUrl() }}" title="{{ $material->title }}" class="h-full w-full" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div></div>@else<p class="mt-3 text-sm text-amber-800">Tautan video ini tidak dapat diputar. Hubungi pengajar untuk memperbaruinya.</p>@endif
                @else
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-3"><p class="text-sm text-slate-500">{{ $material->original_file_name }} · {{ number_format($material->file_size / 1024, 0) }} KB · {{ $material->created_at->format('d M Y') }}</p><a href="{{ route('subject-materials.download', [$subject, $material]) }}" class="inline-flex w-fit items-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Unduh materi</a></div>
                @endif
            </li>@endforeach</ul>
        @endif
    </section>
@endsection
