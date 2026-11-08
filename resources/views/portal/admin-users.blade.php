@extends('layouts.portal')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between" data-reveal>
    <div><p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Akses portal</p><h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Pengguna</h1><p class="mt-2 max-w-2xl text-base leading-7 text-slate-600">Kelola akun guru dan siswa, atur sekolah, atau perbarui data akses.</p></div>
    <div class="flex flex-wrap items-center gap-3">
        <button type="button" data-open-import-user-modal class="inline-flex items-center justify-center rounded-xl bg-teal-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-teal-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-200">Impor akun</button>
        <button type="button" data-open-create-user-modal class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 transition hover:border-teal-700 hover:bg-teal-50 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-100">Buat akun manual</button>
        <a href="{{ route('remote.dashboard') }}" class="text-sm font-bold text-teal-800 underline-offset-4 hover:underline">← Kembali ke ringkasan</a>
    </div>
</div>

<dialog id="import-users-modal" data-open-on-load="{{ $errors->has('file') || $errors->has('import_school_id') ? 'true' : 'false' }}" class="fixed inset-0 m-auto max-h-[92dvh] w-[calc(100%-1.5rem)] max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/55 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-7">
        <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Impor massal</p>
                <h2 id="import-users-title" class="mt-1 text-2xl font-bold tracking-tight text-slate-950">Tambah banyak akun</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Pilih sekolah tujuan dan unggah file Excel atau CSV.</p>
            </div>
            <button type="button" data-close-import-user-modal class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-2xl leading-none text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600" aria-label="Tutup modal">&times;</button>
        </div>

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3">
            <p class="text-sm text-slate-700">Belum punya format file?</p>
            <a href="{{ route('admin.users.template') }}" class="text-sm font-bold text-teal-800 underline underline-offset-4 hover:text-teal-950">Unduh template CSV</a>
        </div>

        @if ($errors->has('file') || $errors->has('import_school_id'))
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-6 text-rose-900" role="alert">
                @foreach ($errors->get('file') as $message)<p>{{ $message }}</p>@endforeach
                @foreach ($errors->get('import_school_id') as $message)<p>{{ $message }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <label for="import-school" class="block text-sm font-semibold text-slate-800">Sekolah tujuan
                <select id="import-school" name="import_school_id" required class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3 py-3 text-base focus:border-teal-600 focus:ring-teal-600">
                    <option value="">Pilih sekolah</option>
                    @foreach ($schools as $school)<option value="{{ $school->id }}" @selected(old('import_school_id') == $school->id)>{{ $school->name }}</option>@endforeach
                </select>
            </label>
            <label for="users-import-file" class="block text-sm font-semibold text-slate-800">File akun (.xlsx atau .csv)
                <input id="users-import-file" type="file" name="file" accept=".xlsx,.csv" required class="mt-1.5 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-slate-100 file:px-4 file:py-3 file:text-sm file:font-bold file:text-slate-800 hover:file:bg-slate-200 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-100" />
            </label>
            <div class="rounded-xl border border-slate-200 px-4 py-3 text-sm leading-6 text-slate-600">
                <p><span class="font-semibold text-slate-800">Kolom wajib:</span> role, nama, email, nisn, password. Role diisi <code class="rounded bg-slate-100 px-1.5 py-0.5">guru</code> atau <code class="rounded bg-slate-100 px-1.5 py-0.5">siswa</code>.</p>
                <p class="mt-2">Password minimal 12 karakter. Email guru dan NISN siswa harus unik. Semua akun di file akan masuk ke sekolah yang dipilih.</p>
                <p class="mt-2">Kolom bidang mapel hanya catatan; penugasan guru diatur di menu Penugasan.</p>
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                <button type="button" data-close-import-user-modal class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Batal</button>
                <button type="submit" @disabled($schools->isEmpty()) class="rounded-xl bg-teal-800 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-teal-900 disabled:cursor-not-allowed disabled:opacity-50">Impor akun</button>
            </div>
        </form>
    </div>
</dialog>

<dialog id="create-user-modal" data-open-on-load="{{ request('action') === 'create' || ($errors->any() && old('role') !== null && ! $errors->has('file') && ! $errors->has('import_school_id')) ? 'true' : 'false' }}" class="fixed inset-0 m-auto max-h-[92dvh] w-[calc(100%-1.5rem)] max-w-4xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-0 shadow-2xl backdrop:bg-slate-950/55 backdrop:backdrop-blur-sm">
    <div class="p-5 sm:p-7">
        <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.1em] text-teal-800">Tambah satu akun</p>
                <h2 id="create-user-modal-title" class="mt-1 text-2xl font-bold tracking-tight text-slate-950">Buat akun manual</h2>
                <p class="mt-1 text-sm leading-6 text-slate-600">Isi data akun khusus tanpa mengunggah file.</p>
            </div>
            <button type="button" data-close-create-user-modal class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-2xl leading-none text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600" aria-label="Tutup modal">&times;</button>
        </div>

    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-base leading-7 text-rose-900" role="alert">
            <p class="font-bold">Akun belum dibuat. Periksa kembali data berikut:</p>
            <ul class="mt-1 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6" data-admin-create-form>
        @csrf
        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 p-5 sm:p-7" aria-labelledby="account-details-title">
                <div class="flex items-start gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-base font-bold text-teal-800">1</span>
                    <div>
                        <h3 id="account-details-title" class="text-xl font-bold text-slate-950">Data akun</h3>
                        <p class="mt-1 text-base leading-7 text-slate-600">Pilih role terlebih dahulu. Sistem akan meminta email atau NISN yang sesuai.</p>
                    </div>
                </div>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <label for="new-user-role" class="block text-base font-semibold text-slate-800">Role dan hak akses
                        <select id="new-user-role" name="role" required data-admin-role class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-lg focus:border-teal-600 focus:ring-teal-600">
                            <option value="guru" @selected(old('role', 'guru') === 'guru')>Guru</option>
                            <option value="siswa" @selected(old('role') === 'siswa')>Siswa</option>
                            <option value="admin_remote" @selected(old('role') === 'admin_remote')>Manajemen All Remote</option>
                            <option value="admin_yayasan" @selected(old('role') === 'admin_yayasan')>Manajemen Yayasan</option>
                        </select>
                    </label>
                    <label for="new-user-school" data-admin-school-wrap class="{{ in_array(old('role', 'guru'), ['guru', 'siswa'], true) ? '' : 'hidden' }} block text-base font-semibold text-slate-800">Sekolah
                        <select id="new-user-school" name="school_id" data-admin-school class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-lg focus:border-teal-600 focus:ring-teal-600">
                            <option value="">Pilih sekolah</option>
                            @foreach ($schools as $school)<option value="{{ $school->id }}" @selected(old('school_id') == $school->id)>{{ $school->name }}</option>@endforeach
                        </select>
                        @if ($schools->isEmpty())<span class="mt-1 block text-sm font-normal text-amber-800">Buat sekolah dahulu di <a href="{{ route('admin.schools') }}" class="font-semibold underline">Sekolah & rombel</a>.</span>@endif
                    </label>
                    <label for="new-user-name" class="block text-base font-semibold text-slate-800">Nama lengkap
                        <input id="new-user-name" name="name" value="{{ old('name') }}" required maxlength="255" autocomplete="name" placeholder="Masukkan nama sesuai identitas" class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-lg focus:border-teal-600 focus:ring-teal-600" />
                    </label>
                    <div class="sm:col-span-2">
                        <p data-admin-role-description class="rounded-xl border border-teal-200 bg-teal-50/70 px-4 py-3 text-base leading-7 text-teal-950" aria-live="polite">Guru dapat mengelola mata pelajaran, materi, dan ujian. Login menggunakan email.</p>
                    </div>
                    <label for="new-user-email" data-admin-email-wrap class="block text-base font-semibold text-slate-800 sm:col-span-2">Email untuk login
                        <input id="new-user-email" name="email" type="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" placeholder="nama@sekolah.id" data-admin-email class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-lg focus:border-teal-600 focus:ring-teal-600" />
                    </label>
                    <label for="new-user-nisn" data-admin-nisn-wrap class="hidden text-base font-semibold text-slate-800 sm:col-span-2">NISN untuk login
                        <input id="new-user-nisn" name="nisn" value="{{ old('nisn') }}" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" placeholder="Masukkan 10 digit NISN" data-admin-nisn class="mt-2 w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-lg focus:border-teal-600 focus:ring-teal-600" />
                        <span class="mt-1.5 block text-sm font-normal text-slate-500">Pastikan NISN benar dan belum digunakan akun lain.</span>
                    </label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200 p-5 sm:p-7" aria-labelledby="account-credentials-title">
                <div class="flex items-start gap-4">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-base font-bold text-amber-800">2</span>
                    <div>
                        <h3 id="account-credentials-title" class="text-xl font-bold text-slate-950">Password awal</h3>
                        <p class="mt-1 text-base leading-7 text-slate-600">Gunakan minimal 12 karakter, dengan huruf besar, huruf kecil, dan angka.</p>
                    </div>
                </div>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="new-user-password" class="block text-base font-semibold text-slate-800">Password</label>
                        <span class="relative mt-2 block">
                            <input id="new-user-password" name="password" type="password" required minlength="12" autocomplete="new-password" data-password-field data-new-password class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 pr-28 text-lg focus:border-teal-600 focus:ring-teal-600" />
                            <button type="button" data-password-toggle="new-user-password" aria-pressed="false" class="absolute inset-y-0 right-2 my-auto rounded-lg px-3 text-sm font-bold text-teal-800 hover:bg-teal-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">Tampilkan</button>
                        </span>
                    </div>
                    <div>
                        <label for="new-user-password-confirmation" class="block text-base font-semibold text-slate-800">Ulangi password</label>
                        <span class="relative mt-2 block">
                            <input id="new-user-password-confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password" data-password-field data-password-confirmation class="w-full rounded-xl border-slate-300 bg-white px-4 py-3 pr-28 text-lg focus:border-teal-600 focus:ring-teal-600" />
                            <button type="button" data-password-toggle="new-user-password-confirmation" aria-pressed="false" class="absolute inset-y-0 right-2 my-auto rounded-lg px-3 text-sm font-bold text-teal-800 hover:bg-teal-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">Tampilkan</button>
                        </span>
                    </div>
                </div>
                <ul class="mt-4 grid gap-2 sm:grid-cols-2" aria-label="Syarat password">
                    @foreach ([['length', 'Minimal 12 karakter'], ['uppercase', 'Huruf besar'], ['lowercase', 'Huruf kecil'], ['number', 'Angka']] as [$requirement, $label])
                        <li data-password-requirement="{{ $requirement }}" class="flex items-center gap-2 text-sm font-medium text-slate-600">
                            <span data-requirement-check class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800 opacity-0" aria-hidden="true">✓</span>
                            {{ $label }}
                        </li>
                    @endforeach
                </ul>
                <p data-password-match-status class="mt-3 text-sm font-medium text-slate-500" role="status" aria-live="polite">Ulangi password untuk memastikan keduanya sama.</p>
                <p class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-base leading-7 text-amber-950">Role admin memiliki akses pengelolaan. Pastikan role yang dipilih memang sesuai kewenangan pengguna. Password dapat diatur ulang dari direktori akun.</p>
            </section>
        </div>

        <div class="mt-5 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-base leading-6 text-slate-500">Pastikan nama dan identitas masuk sudah benar sebelum menyimpan.</p>
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-teal-800 px-5 py-3 text-base font-bold text-white transition hover:bg-teal-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-200">Simpan akun</button>
        </div>
    </form>
    </div>
</dialog>

<section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6" data-reveal>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div><p class="text-sm font-bold uppercase tracking-[0.1em] text-violet-700">Direktori akun</p><h2 class="mt-1 text-2xl font-bold text-slate-950">Pengguna terdaftar <span class="text-base font-semibold text-slate-500">({{ $users->total() }})</span></h2><p class="mt-1 text-sm text-slate-600">Cari, filter, edit, atau hapus akun yang sudah dibuat.</p></div>
        <form method="GET" action="{{ route('admin.users') }}" class="grid gap-2 sm:grid-cols-[minmax(200px,1fr)_160px_200px_210px_auto]" data-admin-user-filter>
            <label class="sr-only" for="user-search">Cari akun</label><input id="user-search" name="q" type="search" value="{{ request('q') }}" placeholder="Cari nama, email, atau NISN" class="rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600" />
            <label class="sr-only" for="role-filter">Filter peran</label><select id="role-filter" name="role" class="rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"><option value="">Semua peran</option><option value="guru" @selected(request('role') === 'guru')>Guru</option><option value="siswa" @selected(request('role') === 'siswa')>Siswa</option><option value="admin_remote" @selected(request('role') === 'admin_remote')>All Remote</option><option value="admin_yayasan" @selected(request('role') === 'admin_yayasan')>Yayasan</option></select>
            <label class="sr-only" for="school-filter">Filter sekolah</label><select id="school-filter" name="school_id" class="rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"><option value="">Semua sekolah</option>@foreach ($schools as $school)<option value="{{ $school->id }}" @selected((string) request('school_id') === (string) $school->id)>{{ $school->name }}</option>@endforeach</select>
            <label class="sr-only" for="attention-filter">Filter tindak lanjut</label><select id="attention-filter" name="attention" class="rounded-xl border-slate-300 text-sm focus:border-teal-600 focus:ring-teal-600"><option value="">Semua status kelas</option><option value="students_without_subject" @selected(request('attention') === 'students_without_subject')>Siswa belum punya kelas</option><option value="teachers_without_subject" @selected(request('attention') === 'teachers_without_subject')>Guru belum punya mapel</option></select>
            <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700">Cari</button>
        </form>
    </div>

    <div class="mt-5 space-y-4">
        @forelse ($users as $user)
            <details class="group rounded-2xl border border-slate-200 bg-white transition-colors open:border-teal-300 open:shadow-sm hover:border-slate-300" name="admin-user-account" data-admin-user-details @if ((string) old('_editing_user_id') === (string) $user->id) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 rounded-2xl p-4 outline-none transition-colors hover:bg-slate-50 focus-visible:ring-4 focus-visible:ring-inset focus-visible:ring-teal-100 sm:px-5 sm:py-4">
                    <span class="min-w-0">
                        <span class="flex flex-wrap items-center gap-2">
                            <span class="truncate text-base font-bold text-slate-950 sm:text-lg">{{ $user->name }}</span>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $user->role === 'siswa' ? 'bg-orange-100 text-orange-800' : ($user->role === 'guru' ? 'bg-teal-100 text-teal-800' : 'bg-violet-100 text-violet-800') }}">{{ ['guru' => 'Guru', 'siswa' => 'Siswa', 'admin_remote' => 'All Remote', 'admin_yayasan' => 'Yayasan'][$user->role] ?? $user->role }}</span>
                            @if (auth()->user()->is($user))<span class="text-xs font-semibold text-slate-500">Akun Anda</span>@endif
                        </span>
                        <span class="mt-1 block truncate text-sm text-slate-600">{{ $user->email ?? 'NISN '.$user->nisn }}@if ($user->school)<span class="text-slate-400"> · </span>{{ $user->school->name }}@endif</span>
                    </span>
                    <span class="flex shrink-0 items-center gap-2 text-sm font-semibold text-slate-600">
                        <span class="hidden sm:inline group-open:hidden">Kelola akun</span>
                        <span class="hidden sm:group-open:inline">Tutup detail</span>
                        <svg class="h-5 w-5 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    </span>
                </summary>

                <div class="border-t border-slate-100 px-4 pb-5 pt-5 sm:px-5">
                    @if ((string) old('_editing_user_id') === (string) $user->id && $errors->any())
                        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-6 text-rose-900" role="alert">
                            @foreach ($errors->all() as $message)<p>{{ $message }}</p>@endforeach
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @csrf @method('PUT')
                        <input type="hidden" name="_editing_user_id" value="{{ $user->id }}" />
                        <label class="text-sm font-semibold text-slate-700">Nama<input name="name" value="{{ (string) old('_editing_user_id') === (string) $user->id ? old('name', $user->name) : $user->name }}" required maxlength="255" class="mt-1.5 w-full rounded-lg border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
                        @if ($user->role === 'siswa')<label class="text-sm font-semibold text-slate-700">NISN<input name="nisn" value="{{ (string) old('_editing_user_id') === (string) $user->id ? old('nisn', $user->nisn) : $user->nisn }}" inputmode="numeric" pattern="[0-9]{10}" maxlength="10" required class="mt-1.5 w-full rounded-lg border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>@else<label class="text-sm font-semibold text-slate-700">Email<input name="email" type="email" value="{{ (string) old('_editing_user_id') === (string) $user->id ? old('email', $user->email) : $user->email }}" required class="mt-1.5 w-full rounded-lg border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>@endif
                        @if (in_array($user->role, ['guru', 'siswa'], true))
                            <label class="text-sm font-semibold text-slate-700 sm:col-span-2 xl:col-span-1">Sekolah<select name="school_id" required class="mt-1.5 w-full rounded-lg border-slate-300 bg-white text-base focus:border-teal-600 focus:ring-teal-600">@foreach ($schools as $school)<option value="{{ $school->id }}" @selected((int) ((string) old('_editing_user_id') === (string) $user->id ? old('school_id', $user->school_id) : $user->school_id) === (int) $school->id)>{{ $school->name }}</option>@endforeach</select></label>
                        @endif
                        <label class="text-sm font-semibold text-slate-700">Password baru <span class="font-normal text-slate-500">(opsional)</span><input name="password" type="password" minlength="12" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
                        <label class="text-sm font-semibold text-slate-700">Ulangi password baru<input name="password_confirmation" type="password" minlength="12" autocomplete="new-password" class="mt-1.5 w-full rounded-lg border-slate-300 text-base focus:border-teal-600 focus:ring-teal-600" /></label>
                        <p class="self-end text-sm leading-6 text-slate-500">Peran akun dipertahankan agar relasi kelas tetap aman.</p>
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4 sm:col-span-2 xl:col-span-3">
                            <button type="submit" class="rounded-lg bg-teal-800 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-teal-900">Simpan perubahan</button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="mt-3 flex justify-end" data-admin-delete-form data-account-name="{{ $user->name }}">
                        @csrf @method('DELETE')
                        <button type="submit" @disabled(auth()->user()->is($user)) class="rounded-lg border border-rose-200 px-4 py-2.5 text-sm font-bold text-rose-800 transition hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-40" title="Hapus akun">Hapus akun</button>
                    </form>
                </div>
            </details>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center"><p class="font-semibold text-slate-800">Tidak ada akun yang cocok.</p><a href="{{ route('admin.users') }}" class="mt-2 inline-block text-sm font-bold text-teal-800 underline">Hapus filter</a></div>
        @endforelse
    </div>
    <div class="mt-5">{{ $users->links() }}</div>
</section>
@endsection
