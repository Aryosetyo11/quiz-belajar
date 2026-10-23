@extends('layouts.portal')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
    <div><p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Akses portal</p><h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950">Manajemen akun</h1><p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Buat dan perbarui akun guru, siswa, serta admin yayasan.</p></div>
    <a href="{{ route('remote.dashboard') }}" class="text-sm font-bold text-teal-800 underline-offset-4 hover:underline">← Kembali ke ringkasan</a>
</div>

@if (session('status'))<div class="mt-5 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-sm font-semibold text-teal-900" role="status">{{ session('status') }}</div>@endif
@if ($errors->has('user'))<div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-900" role="alert">{{ $errors->first('user') }}</div>@endif

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
    <div><p class="text-sm font-bold uppercase tracking-[0.1em] text-orange-700">Tambah akses</p><h2 class="mt-1 text-xl font-bold text-slate-950">Buat akun baru</h2><p class="mt-1 text-sm text-slate-600">Password minimal 12 karakter, harus berisi huruf besar, huruf kecil, dan angka.</p></div>
    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3" data-admin-create-form>
        @csrf
        <label class="text-sm font-semibold text-slate-700">Nama lengkap<input name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" class="mt-1.5 w-full rounded-xl border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
        <label class="text-sm font-semibold text-slate-700">Peran<select name="role" required data-admin-role class="mt-1.5 w-full rounded-xl border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600"><option value="guru" @selected(old('role') === 'guru')>Guru</option><option value="siswa" @selected(old('role') === 'siswa')>Siswa</option><option value="admin_remote" @selected(old('role') === 'admin_remote')>Manajemen All Remote</option><option value="admin_yayasan" @selected(old('role') === 'admin_yayasan')>Manajemen Yayasan</option></select></label>
        <label class="text-sm font-semibold text-slate-700" data-admin-email-wrap>Email<input name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" data-admin-email class="mt-1.5 w-full rounded-xl border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
        <label class="hidden text-sm font-semibold text-slate-700" data-admin-nisn-wrap>NISN (10 digit)<input name="nisn" value="{{ old('nisn') }}" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" data-admin-nisn class="mt-1.5 w-full rounded-xl border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
        <label class="text-sm font-semibold text-slate-700">Password<input name="password" type="password" required minlength="12" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
        <label class="text-sm font-semibold text-slate-700">Ulangi password<input name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" class="mt-1.5 w-full rounded-xl border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
        <div class="sm:col-span-2 xl:col-span-3"><button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-teal-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-200">Buat akun</button></div>
    </form>
    @foreach ($errors->getMessages() as $field => $messages)
        @if ($field !== 'user')<p class="mt-2 text-sm font-medium text-rose-700">{{ implode(' ', $messages) }}</p>@endif
    @endforeach
</section>

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-sm font-bold uppercase tracking-[0.1em] text-violet-700">Direktori</p><h2 class="mt-1 text-xl font-bold text-slate-950">Akun terdaftar <span class="text-sm font-semibold text-slate-500">({{ $users->total() }})</span></h2></div>
        <form method="GET" action="{{ route('admin.users') }}" class="grid gap-2 sm:grid-cols-[minmax(200px,1fr)_170px_auto]" data-admin-user-filter>
            <label class="sr-only" for="user-search">Cari akun</label><input id="user-search" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama, email, atau NISN" class="rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" />
            <label class="sr-only" for="role-filter">Filter peran</label><select id="role-filter" name="role" class="rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"><option value="">Semua peran</option><option value="guru" @selected(request('role') === 'guru')>Guru</option><option value="siswa" @selected(request('role') === 'siswa')>Siswa</option><option value="admin_remote" @selected(request('role') === 'admin_remote')>All Remote</option><option value="admin_yayasan" @selected(request('role') === 'admin_yayasan')>Yayasan</option></select>
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">Cari</button>
        </form>
    </div>

    <div class="mt-5 space-y-4">
        @forelse ($users as $user)
            <article class="rounded-2xl border border-slate-200 p-4 transition hover:border-teal-200 hover:bg-teal-50/30 sm:p-5" data-admin-user-card>
                <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                    <div class="min-w-0"><div class="flex flex-wrap items-center gap-2"><h3 class="font-bold text-slate-950">{{ $user->name }}</h3><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->role === 'siswa' ? 'bg-orange-100 text-orange-800' : ($user->role === 'guru' ? 'bg-teal-100 text-teal-800' : 'bg-violet-100 text-violet-800') }}">{{ ['guru' => 'Guru', 'siswa' => 'Siswa', 'admin_remote' => 'All Remote', 'admin_yayasan' => 'Yayasan'][$user->role] ?? $user->role }}</span>@if (auth()->user()->is($user))<span class="text-xs font-semibold text-slate-500">Akun Anda</span>@endif</div><p class="mt-1 break-all text-sm text-slate-600">{{ $user->email ?? 'NISN '.$user->nisn }}</p></div>
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid flex-1 gap-3 sm:grid-cols-2 xl:max-w-2xl">
                        @csrf @method('PUT')
                        <label class="text-xs font-semibold text-slate-600">Nama<input name="name" value="{{ $user->name }}" required maxlength="255" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" /></label>
                        @if ($user->role === 'siswa')<label class="text-xs font-semibold text-slate-600">NISN<input name="nisn" value="{{ $user->nisn }}" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" /></label>@else<label class="text-xs font-semibold text-slate-600">Email<input name="email" type="email" value="{{ $user->email }}" required class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" /></label>@endif
                        <label class="text-xs font-semibold text-slate-600 sm:col-span-2">Password baru <span class="font-normal">(kosongkan jika tidak diganti)</span><input name="password" type="password" minlength="12" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" /></label>
                        <label class="text-xs font-semibold text-slate-600 sm:col-span-2">Ulangi password baru<input name="password_confirmation" type="password" minlength="12" autocomplete="new-password" class="mt-1 w-full rounded-lg border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" /></label>
                        <div class="flex flex-wrap items-center gap-3 sm:col-span-2"><button class="rounded-lg bg-teal-800 px-4 py-2 text-sm font-bold text-white transition hover:bg-teal-900">Simpan perubahan</button><span class="text-xs text-slate-500">Peran dipertahankan untuk menjaga relasi kelas.</span></div>
                    </form>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-admin-delete-form data-account-name="{{ $user->name }}">
                        @csrf @method('DELETE')<button type="submit" @disabled(auth()->user()->is($user)) class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-bold text-rose-800 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-40" title="Hapus akun">Hapus</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center"><p class="font-semibold text-slate-800">Tidak ada akun yang cocok.</p><a href="{{ route('admin.users') }}" class="mt-2 inline-block text-sm font-bold text-teal-800 underline">Hapus filter</a></div>
        @endforelse
    </div>
    <div class="mt-5">{{ $users->links() }}</div>
</section>
@endsection
