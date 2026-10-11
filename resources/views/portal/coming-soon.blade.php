@extends('layouts.portal')

@section('title', $pageTitle.' | RuangUji')

@section('content')
    <div class="max-w-4xl">
        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="inline-flex items-center gap-2 text-base font-medium text-slate-600 transition hover:text-slate-950">
            <span aria-hidden="true">&larr;</span> Dashboard
        </a>

        <header class="mt-7">
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Ruang kerja</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">{{ $pageTitle }}</h1>
                <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-medium text-amber-800">Segera</span>
            </div>
            <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600">{{ $pageDescription }}</p>
        </header>

        <section class="mt-8 rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center sm:px-8" aria-labelledby="empty-page-title">
            <span class="mx-auto flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3.75h7l5 5V20a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2V5.75a2 2 0 0 1 2-2Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 4v5h5M9 14h6m-6 3h6"/></svg>
            </span>
            <h2 id="empty-page-title" class="mt-4 text-xl font-semibold text-slate-950">Halaman ini sedang disiapkan</h2>
            <p class="mx-auto mt-2 max-w-lg text-base leading-7 text-slate-600">Belum ada data atau tindakan yang bisa dilakukan di sini. Modul ini akan diaktifkan setelah proses pengembangannya selesai.</p>
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="mt-6 inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50">Kembali ke dashboard</a>
        </section>
    </div>
@endsection
