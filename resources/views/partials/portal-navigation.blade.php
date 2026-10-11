@php
    $isTeacher = auth()->user()->role === \App\Models\User::ROLE_TEACHER;
@endphp

<nav class="space-y-7" aria-label="Navigasi portal">
    <section>
        <h2 class="px-3 text-sm font-semibold uppercase tracking-[0.08em] text-slate-500">Menu utama</h2>
        <ul class="mt-3 space-y-1.5">
            <li>
                <a href="{{ route(auth()->user()->dashboardRoute()) }}" @class(['flex items-center gap-3 rounded-lg px-3 py-3 text-base font-medium transition', 'bg-indigo-50 text-indigo-800' => request()->routeIs('teacher.dashboard', 'student.dashboard'), 'text-slate-600 hover:bg-slate-50 hover:text-slate-950' => ! request()->routeIs('teacher.dashboard', 'student.dashboard')])>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6" /></svg>
                    Dashboard
                </a>
            </li>

            @php
                $modules = $isTeacher
                    ? [
                        ['Mata pelajaran', 'teacher.subjects', false],
                        ['Bank soal', 'teacher.question-bank', true],
                        ['Sesi ujian', 'teacher.exam-sessions', true],
                        ['Pemantauan', 'teacher.monitoring', true],
                        ['Hasil penilaian', 'teacher.results', true],
                    ]
                    : [
                        ['Mata pelajaran', 'student.subjects', false],
                        ['Ujian saya', 'student.exams', true],
                        ['Hasil belajar', 'student.results', true],
                    ];
            @endphp

            @foreach ($modules as [$label, $routeName, $comingSoon])
                <li>
                    <a href="{{ route($routeName) }}" @class(['flex items-center justify-between gap-3 rounded-lg px-3 py-3 text-base transition', 'bg-indigo-50 font-medium text-indigo-800' => request()->routeIs($routeName), 'text-slate-700 hover:bg-slate-50 hover:text-slate-950' => ! request()->routeIs($routeName)])>
                        <span>{{ $label }}</span>
                        @if ($comingSoon)
                            <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-sm font-medium text-slate-600">Segera</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section>
        <h2 class="px-3 text-sm font-semibold uppercase tracking-[0.08em] text-slate-500">Pengaturan</h2>
        <ul class="mt-3">
            <li>
                <a href="{{ route('profile.edit') }}" @class(['flex items-center gap-3 rounded-lg px-3 py-3 text-base font-medium transition', 'bg-indigo-50 text-indigo-800' => request()->routeIs('profile.edit'), 'text-slate-700 hover:bg-slate-50 hover:text-slate-950' => ! request()->routeIs('profile.edit')])>
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                    Profil saya
                </a>
            </li>
        </ul>
    </section>
</nav>
