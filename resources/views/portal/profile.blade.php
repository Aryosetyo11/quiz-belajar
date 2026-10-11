@extends('layouts.portal')

@section('title', 'Profil Saya | RuangUji')

@section('content')
    @php
        $user = auth()->user();
        $loginIdentifier = $user->role === \App\Models\User::ROLE_TEACHER ? $user->email : $user->nisn;
        $identifierLabel = $user->role === \App\Models\User::ROLE_TEACHER ? 'Email masuk' : 'NISN';
        $roleLabel = $user->role === \App\Models\User::ROLE_TEACHER ? 'Pengajar' : 'Siswa';
    @endphp

    <div class="max-w-3xl">
        <a href="{{ route($user->dashboardRoute()) }}" class="inline-flex items-center gap-2 text-base font-medium text-slate-600 transition hover:text-slate-950">
            <span aria-hidden="true">&larr;</span> Kembali ke dashboard
        </a>

        <header class="mt-7">
            <p class="text-sm font-semibold uppercase tracking-[0.1em] text-indigo-700">Pengaturan akun</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Profil saya</h1>
            <p class="mt-3 text-base leading-7 text-slate-600">Perbarui nama yang ditampilkan pada portal RuangUji.</p>
        </header>

        @if (session('status'))
            <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-base text-emerald-800" role="status">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="mt-6 rounded-xl border border-slate-200 bg-white p-5 sm:p-7">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-2 block text-base font-medium text-slate-800">Nama lengkap</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-base text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 @error('name') border-red-500 @enderror" aria-describedby="name-error">
                @error('name')
                    <p id="name-error" class="mt-2 text-base text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <span class="mb-2 block text-base font-medium text-slate-800">{{ $identifierLabel }}</span>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-3 text-base text-slate-600">{{ $loginIdentifier ?: 'Belum diatur' }}</div>
                </div>
                <div>
                    <span class="mb-2 block text-base font-medium text-slate-800">Peran</span>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-3.5 py-3 text-base text-slate-600">{{ $roleLabel }}</div>
                </div>
            </div>

            <p class="mt-5 text-base leading-7 text-slate-600">Email atau NISN dipakai untuk masuk. Hubungi administrator yayasan jika data tersebut perlu diperbaiki.</p>

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route($user->dashboardRoute()) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-3 text-base font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-3 text-base font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Simpan perubahan</button>
            </div>
        </form>
    </div>
@endsection
