@php
    $isAdmin = in_array(auth()->user()->role, [\App\Models\User::ROLE_ADMIN_REMOTE, \App\Models\User::ROLE_ADMIN_FOUNDATION], true);
    $isFoundationAdmin = auth()->user()->role === \App\Models\User::ROLE_ADMIN_FOUNDATION;
    $isTeacher = auth()->user()->role === \App\Models\User::ROLE_TEACHER;
@endphp

<nav class="space-y-6" aria-label="Navigasi portal">
    <section>
        <h2 class="px-3 text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Menu utama</h2>
        <ul class="mt-2 space-y-0.5">
            <li>
                <a href="{{ route(auth()->user()->dashboardRoute()) }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition duration-150', 'bg-indigo-50 text-indigo-800 shadow-sm' => request()->routeIs('teacher.dashboard', 'student.dashboard', 'remote.dashboard', 'foundation.dashboard'), 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('teacher.dashboard', 'student.dashboard', 'remote.dashboard', 'foundation.dashboard')])>
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.5 9v11h13V9M9 20v-6h6v6" /></svg>
                    {{ $isFoundationAdmin ? 'Ringkasan' : 'Dashboard' }}
                </a>
            </li>

            @php
                $svgIcons = [
                    'Manajemen akun'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
                    'Sekolah & rombel'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15a.75.75 0 0 1 .75.75V21h-16.5V3.75A.75.75 0 0 1 4.5 3ZM9 7.5h.008v.008H9V7.5Zm0 3h.008v.008H9V10.5Zm0 3h.008v.008H9v-.008Zm6-3h.008v.008H15V10.5Zm0 3h.008v.008H15v-.008ZM9 21v-3.75a3 3 0 0 1 6 0V21"/>',
                    'Data sekolah'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15a.75.75 0 0 1 .75.75V21h-16.5V3.75A.75.75 0 0 1 4.5 3ZM9 7.5h.008v.008H9V7.5Zm0 3h.008v.008H9V10.5Zm0 3h.008v.008H9v-.008Zm6-3h.008v.008H15V10.5Zm0 3h.008v.008H15v-.008ZM9 21v-3.75a3 3 0 0 1 6 0V21"/>',
                    'Laporan'           => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v17.25h17.25M7.5 14.25l3-3 3 3 5.25-6"/>',
                    'Penugasan mapel'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>',
                    'Mata pelajaran'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>',
                    'Bank soal'         => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>',
                    'Sesi ujian'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>',
                    'Pemantauan'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
                    'Hasil penilaian'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
                    'Ujian saya'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>',
                    'Hasil belajar'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
                ];

                $modules = $isFoundationAdmin
                    ? [
                        ['Data sekolah', 'foundation.schools', false],
                        ['Laporan', 'foundation.reports', false],
                    ]
                    : ($isAdmin
                    ? [
                        ...[
                            ['Manajemen akun', 'admin.users', false],
                            ['Sekolah & rombel', 'admin.schools', false],
                            ['Penugasan mapel', 'admin.assignments', false],
                            ['Sesi ujian', 'admin.exam-sessions', false],
                            ['Bank soal', 'admin.question-bank', false],
                        ],
                    ]
                    : ($isTeacher
                    ? [
                        ['Mata pelajaran', 'teacher.subjects', false],
                        ['Bank soal', 'teacher.question-bank', false],
                        ['Sesi ujian', 'teacher.exam-sessions', false],
                        ['Pemantauan', 'teacher.monitoring', false],
                        ['Hasil penilaian', 'teacher.results', false],
                    ]
                    : [
                        ['Mata pelajaran', 'student.subjects', false],
                        ['Ujian saya', 'student.exams', false],
                        ['Hasil belajar', 'student.results', false],
                    ]));
            @endphp

            @foreach ($modules as [$label, $routeName, $comingSoon])
                <li>
                    <a href="{{ route($routeName) }}" @class(['flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 text-sm transition duration-150', 'bg-indigo-50 font-semibold text-indigo-800 shadow-sm' => request()->routeIs($routeName, $routeName.'.*'), 'font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs($routeName, $routeName.'.*')])>
                        <span class="flex items-center gap-3">
                            @if (isset($svgIcons[$label]))
                                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $svgIcons[$label] !!}</svg>
                            @endif
                            {{ $label }}
                        </span>
                        @if ($comingSoon)
                            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">Segera</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <section>
        <h2 class="px-3 text-xs font-bold uppercase tracking-[0.1em] text-slate-400">Pengaturan</h2>
        <ul class="mt-2">
            <li>
                <a href="{{ route('profile.edit') }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition duration-150', 'bg-indigo-50 text-indigo-800 shadow-sm' => request()->routeIs('profile.edit'), 'font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! request()->routeIs('profile.edit')])>
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21a8 8 0 0 0-16 0m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                    Profil saya
                </a>
            </li>
        </ul>
    </section>
</nav>
