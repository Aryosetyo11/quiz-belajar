@extends('layouts.auth')

@section('title', 'Masuk ke Portal | RuangUji')

@section('content')
@php
    $isTeacher = $role === \App\Models\User::ROLE_TEACHER;
    $identifierField = $isTeacher ? 'email' : 'nisn';
    $identifierLabel = $isTeacher ? 'Email' : 'NISN';
    $loginRoute = $isTeacher ? 'login.teacher.store' : 'login.student.store';
@endphp

<div class="mx-auto max-w-md">
    @include('partials.portal-brand')

    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_12px_40px_-32px_rgba(15,23,42,0.28)] sm:p-8">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-600 transition hover:text-indigo-700">
            <span aria-hidden="true">&larr;</span> Pilih portal
        </a>

        <div class="mt-7">
            <p class="text-sm font-medium text-indigo-700">Portal {{ $isTeacher ? 'Pengajar' : 'Siswa' }}</p>
            <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">Masuk ke akun Anda</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan {{ strtolower($identifierLabel) }} dan kata sandi yang terdaftar.</p>
        </div>

        <form class="mt-7 space-y-5" method="POST" action="{{ route($loginRoute) }}">
            @csrf

            <div>
                <label for="{{ $identifierField }}" class="mb-2 block text-sm font-medium text-slate-800">{{ $identifierLabel }}</label>
                <input id="{{ $identifierField }}" name="{{ $identifierField }}" type="{{ $isTeacher ? 'email' : 'text' }}" @if ($isTeacher) autocomplete="username" @else inputmode="numeric" pattern="[0-9]{10}" maxlength="10" autocomplete="username" @endif value="{{ old($identifierField) }}" required autofocus class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-base text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 @error($identifierField) border-red-500 @enderror" aria-describedby="{{ $identifierField }}-error">
                @error($identifierField)
                    <p id="{{ $identifierField }}-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-800">Kata sandi</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required class="block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-base text-slate-950 outline-none transition focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 @error('password') border-red-500 @enderror" aria-describedby="password-error">
                @error('password')
                    <p id="password-error" class="mt-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-slate-300">Masuk</button>
        </form>
    </section>

    <p class="mt-5 text-sm text-slate-500">Kendala akses? Hubungi administrator yayasan.</p>
</div>
@endsection
