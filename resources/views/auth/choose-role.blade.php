@extends('layouts.auth')

@section('title', 'Masuk Portal | RuangUji')

@section('content')
<div class="mx-auto max-w-2xl">
    @include('partials.portal-brand')

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_12px_40px_-32px_rgba(15,23,42,0.28)]">
        <div class="px-6 pb-6 pt-7 sm:px-8 sm:pt-8">
            <p class="text-sm font-medium text-indigo-700">Portal pengguna</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Masuk ke RuangUji</h1>
            <p class="mt-2 max-w-lg text-sm leading-6 text-slate-600">Pilih jenis akun yang terdaftar untuk melanjutkan.</p>
        </div>

        <div class="grid gap-3 px-4 pb-4 sm:grid-cols-2 sm:px-8 sm:pb-8">
            <a href="{{ route('login.teacher') }}" class="group flex min-h-36 flex-col rounded-xl border border-slate-200 p-5 transition duration-200 hover:border-indigo-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-colors group-hover:bg-indigo-50 group-hover:text-indigo-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 7a7 7 0 0 1 14 0" />
                    </svg>
                </span>
                <span class="mt-4 flex items-center justify-between gap-4">
                    <span>
                        <span class="block text-base font-semibold text-slate-900">Pengajar</span>
                        <span class="mt-1 block text-sm leading-5 text-slate-600">Masuk dengan email dan kata sandi.</span>
                    </span>
                    <span class="text-lg text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-indigo-700" aria-hidden="true">&rarr;</span>
                </span>
            </a>

            <a href="{{ route('login.student') }}" class="group flex min-h-36 flex-col rounded-xl border border-slate-200 p-5 transition duration-200 hover:border-indigo-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700 transition-colors group-hover:bg-indigo-50 group-hover:text-indigo-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v17H6.5A2.5 2.5 0 0 0 4 22V5.5ZM20 5.5A2.5 2.5 0 0 0 17.5 3H13v17h4.5A2.5 2.5 0 0 1 20 22V5.5Z" />
                    </svg>
                </span>
                <span class="mt-4 flex items-center justify-between gap-4">
                    <span>
                        <span class="block text-base font-semibold text-slate-900">Siswa</span>
                        <span class="mt-1 block text-sm leading-5 text-slate-600">Masuk dengan NISN dan kata sandi.</span>
                    </span>
                    <span class="text-lg text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-indigo-700" aria-hidden="true">&rarr;</span>
                </span>
            </a>
        </div>

        <div class="border-t border-slate-100 bg-slate-50/70 px-6 py-4 text-sm text-slate-600 sm:px-8">
            Belum memiliki akun? Hubungi administrator yayasan.
        </div>
    </section>

    <a href="{{ route('home') }}" class="mt-6 inline-flex text-sm font-medium text-slate-600 transition hover:text-slate-950"><span aria-hidden="true" class="mr-2">&larr;</span>Kembali ke halaman utama</a>
</div>
@endsection
