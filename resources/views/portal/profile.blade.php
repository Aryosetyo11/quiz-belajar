@extends('layouts.portal')

@section('title', 'Akun Saya | RuangUji')

@section('content')
    @php
        $user = auth()->user()->loadMissing('school');
        $isStudent = $user->role === \App\Models\User::ROLE_STUDENT;
        $loginIdentifier = $isStudent ? $user->nisn : $user->email;
        $identifierLabel = $isStudent ? 'NISN untuk masuk' : 'Email untuk masuk';
        $roleLabel = match ($user->role) {
            \App\Models\User::ROLE_TEACHER => 'Pengajar',
            \App\Models\User::ROLE_STUDENT => 'Siswa',
            \App\Models\User::ROLE_ADMIN_REMOTE => 'Manajemen All Remote',
            \App\Models\User::ROLE_ADMIN_FOUNDATION => 'Manajemen Yayasan',
            default => 'Pengguna portal',
        };
        $roleGuidance = match ($user->role) {
            \App\Models\User::ROLE_STUDENT => 'NISN digunakan saat masuk ke portal siswa. Jika NISN tidak sesuai, minta pengajar atau administrator memperbaikinya.',
            \App\Models\User::ROLE_TEACHER => 'Email digunakan saat masuk ke portal pengajar. Hubungi administrator jika alamat email akun perlu diperbaiki.',
            \App\Models\User::ROLE_ADMIN_REMOTE => 'Email digunakan saat masuk ke portal administrator. Perubahan alamat email dapat dilakukan melalui Manajemen Akun.',
            \App\Models\User::ROLE_ADMIN_FOUNDATION => 'Email digunakan saat masuk ke portal yayasan. Hubungi administrator All Remote jika alamat email akun perlu diperbaiki.',
            default => 'Email digunakan saat masuk ke portal. Hubungi administrator jika alamat email akun perlu diperbaiki.',
        };
        $initials = \Illuminate\Support\Str::of($user->name)
            ->trim()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_substr($part, 0, 1))
            ->implode('');
    @endphp

    <div class="w-full space-y-7">
        <header class="flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-sm font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-600/10">Pengaturan</p>
                <h1 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">Akun saya</h1>
                <p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Kelola nama yang terlihat di RuangUji dan periksa identitas yang digunakan untuk masuk.</p>
            </div>
            <a href="{{ route($user->dashboardRoute()) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali ke dashboard</a>
        </header>

        <div class="grid gap-5 lg:grid-cols-[minmax(250px,0.8fr)_minmax(0,1.6fr)] lg:items-start">
            <aside class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <p class="text-sm font-semibold text-slate-500">Ringkasan akun</p>
                <div class="mt-5 flex items-center gap-4">
                    <span @class([
                        'flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-lg font-bold',
                        'bg-indigo-100 text-indigo-800' => $user->role === \App\Models\User::ROLE_TEACHER,
                        'bg-blue-100 text-blue-800' => $user->role === \App\Models\User::ROLE_STUDENT,
                        'bg-teal-100 text-teal-800' => $user->role === \App\Models\User::ROLE_ADMIN_REMOTE,
                        'bg-violet-100 text-violet-800' => $user->role === \App\Models\User::ROLE_ADMIN_FOUNDATION,
                    ])>{{ $initials }}</span>
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-semibold text-slate-950">{{ $user->name }}</h2>
                        <span @class([
                            'mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-indigo-50 text-indigo-800' => $user->role === \App\Models\User::ROLE_TEACHER,
                            'bg-blue-50 text-blue-800' => $user->role === \App\Models\User::ROLE_STUDENT,
                            'bg-teal-50 text-teal-800' => $user->role === \App\Models\User::ROLE_ADMIN_REMOTE,
                            'bg-violet-50 text-violet-800' => $user->role === \App\Models\User::ROLE_ADMIN_FOUNDATION,
                        ])>{{ $roleLabel }}</span>
                    </div>
                </div>

                <dl class="mt-6 space-y-4 border-t border-slate-100 pt-5">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $identifierLabel }}</dt>
                        <dd class="mt-1 break-all text-base font-medium text-slate-900">{{ $loginIdentifier ?: 'Belum diatur' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Role portal</dt>
                        <dd class="mt-1 text-base font-medium text-slate-900">{{ $roleLabel }}</dd>
                    </div>
                    @if ($user->school)
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sekolah</dt>
                            <dd class="mt-1 text-base font-medium text-slate-900">{{ $user->school->name }}</dd>
                        </div>
                    @endif
                </dl>

                <p class="mt-5 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">{{ $roleGuidance }}</p>
            </aside>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7" aria-labelledby="profile-form-heading">
                <div class="border-b border-slate-100 pb-5">
                    <h2 id="profile-form-heading" class="text-xl font-semibold text-slate-950">Informasi profil</h2>
                    <p class="mt-1 text-sm leading-6 text-slate-600">Nama ini ditampilkan pada dashboard, kelas, dan hasil ujian.</p>
                </div>

                <form method="POST" action="{{ route('profile.update') }}" class="mt-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="name" class="mb-2 block text-sm font-semibold text-slate-800">Nama lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name" aria-describedby="name-help name-error" class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-3 text-base text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 @error('name') border-rose-500 @enderror">
                        <p id="name-help" class="mt-2 text-sm text-slate-500">Gunakan nama yang mudah dikenali oleh siswa dan pengajar.</p>
                        @error('name')
                            <p id="name-error" class="mt-2 text-sm font-medium text-rose-700" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <a href="{{ route($user->dashboardRoute()) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Simpan nama</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
@endsection
