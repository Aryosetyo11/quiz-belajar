@extends('layouts.portal')

@section('title', $isFoundationAdmin ? 'Dashboard Yayasan' : 'Dashboard All Remote')

@section('content')
<div class="overflow-hidden rounded-[28px] border border-amber-200 bg-gradient-to-br from-[#fff7e8] via-white to-[#e9f8f3] p-6 shadow-sm sm:p-9" data-reveal>
    <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
        <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-teal-200 bg-white/80 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.12em] text-teal-800"><span class="h-2 w-2 rounded-full bg-teal-500"></span>{{ $isFoundationAdmin ? 'Yayasan - Ringkasan institusi' : 'All Remote - Pusat pengelolaan' }}</div>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">Selamat datang, {{ auth()->user()->name }}</h1>
            <p class="mt-3 text-base leading-7 text-slate-600">{{ $isFoundationAdmin ? 'Lihat gambaran jumlah siswa, guru, dan aktivitas pembelajaran di seluruh institusi.' : 'Kelola akses pengguna dan pantau ringkasan aktivitas portal.' }}</p>
        </div>
        @if (! $isFoundationAdmin)
            <a href="{{ route('admin.users') }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-teal-900 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-teal-200">Kelola akun <span aria-hidden="true">&rarr;</span></a>
        @endif
    </div>
</div>

<section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan institusi">
    @foreach ([['Total siswa', $studentCount, 'bg-orange-100 text-orange-800', 'S'], ['Total guru', $teacherCount, 'bg-teal-100 text-teal-800', 'G'], ['Mata pelajaran', $subjectCount, 'bg-violet-100 text-violet-800', 'M'], ['Pendaftaran kelas', $enrollmentCount, 'bg-amber-100 text-amber-800', 'K']] as [$label, $value, $color, $icon])
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_8px_30px_-24px_rgba(15,23,42,0.3)] transition duration-300 hover:-translate-y-1 hover:shadow-md sm:p-6" data-reveal>
            <div class="flex items-center justify-between"><p class="text-sm font-semibold text-slate-600">{{ $label }}</p><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $color }} text-sm font-extrabold" aria-hidden="true">{{ $icon }}</span></div>
            <p class="mt-5 text-3xl font-bold tracking-tight text-slate-950">{{ number_format($value, 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-500">Data terkini dari portal</p>
        </article>
    @endforeach
</section>

<section class="mt-7 grid gap-5 xl:grid-cols-[minmax(0,1.35fr)_minmax(300px,0.85fr)]" aria-label="Analitik institusi">
    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" data-reveal>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div><p class="text-xs font-bold uppercase tracking-[0.12em] text-teal-700">Komposisi pengguna</p><h2 class="mt-1.5 text-xl font-bold tracking-tight text-slate-950">Komunitas belajar</h2><p class="mt-1 text-sm text-slate-500">Perbandingan akun berdasarkan peran.</p></div>
            <div class="inline-flex w-fit rounded-xl bg-slate-100 p-1" role="group" aria-label="Pilih tampilan grafik">
                <button type="button" data-chart-mode="count" aria-pressed="true" class="rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-slate-800 shadow-sm">Jumlah</button>
                <button type="button" data-chart-mode="percentage" aria-pressed="false" class="rounded-lg px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900">Persentase</button>
            </div>
        </div>
        <div class="mt-7 grid items-center gap-7 md:grid-cols-[minmax(190px,0.8fr)_minmax(0,1.2fr)]">
            <div data-chart-donut class="relative mx-auto aspect-square w-full max-w-[220px] rounded-full transition-transform duration-300" style="background: {{ $donutBackground }}" role="img" aria-label="Diagram komposisi {{ $totalAccounts }} akun">
                <div class="absolute inset-[20%] flex flex-col items-center justify-center rounded-full bg-white text-center shadow-inner"><span data-chart-total class="text-3xl font-bold tracking-tight text-slate-950">{{ number_format($totalAccounts, 0, ',', '.') }}</span><span data-chart-total-label class="mt-1 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-500">total akun</span></div>
            </div>
            <div class="space-y-5">
                @foreach ($chartData as $item)
                    @php($barWidth = $totalAccounts > 0 ? ($item['count'] / $totalAccounts) * 100 : 0)
                    <div data-chart-item data-label="{{ $item['label'] }}" data-count="{{ $item['count'] }}" data-percentage="{{ $item['percentage'] }}" data-color="{{ $item['color'] }}">
                        <button type="button" data-chart-filter aria-pressed="false" class="mb-2 flex w-full items-center justify-between gap-3 rounded-lg text-left transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600"><span class="flex items-center gap-2.5 text-sm font-semibold text-slate-700"><span class="h-3 w-3 rounded-full" style="background: {{ $item['color'] }}"></span>{{ $item['label'] }}</span><span class="text-sm font-bold tabular-nums text-slate-950"><span data-chart-value>{{ number_format($item['count'], 0, ',', '.') }}</span> <span data-chart-unit>akun</span></span></button>
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="{{ $item['label'] }}" aria-valuemin="0" aria-valuemax="{{ $totalAccounts }}" aria-valuenow="{{ $item['count'] }}"><div data-chart-bar class="h-full rounded-full transition-[width] duration-500 ease-out" style="width: {{ $barWidth }}%; background: {{ $item['color'] }}"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4"><p data-chart-summary class="text-sm text-slate-600" aria-live="polite">Menampilkan semua role dalam grafik.</p><button type="button" data-chart-reset class="rounded-lg px-3 py-2 text-sm font-semibold text-teal-800 transition hover:bg-teal-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">Tampilkan semua</button></div>
    </article>

    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" data-reveal>
        <p class="text-xs font-bold uppercase tracking-[0.12em] text-orange-700">Aktivitas pembelajaran</p><h2 class="mt-1.5 text-xl font-bold tracking-tight text-slate-950">Ekosistem belajar</h2><p class="mt-1 text-sm text-slate-500">Materi dan pengelolaan yang tercatat.</p>
        <div class="mt-6 divide-y divide-slate-100 rounded-xl border border-slate-100 px-4">
            <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm font-medium text-slate-600">Materi belajar</span><strong class="text-xl font-bold tabular-nums text-violet-800">{{ number_format($materialCount, 0, ',', '.') }}</strong></div>
            <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm font-medium text-slate-600">Admin All Remote</span><strong class="text-xl font-bold tabular-nums text-teal-800">{{ number_format($remoteAdminCount, 0, ',', '.') }}</strong></div>
            <div class="flex items-center justify-between gap-4 py-4"><span class="text-sm font-medium text-slate-600">Admin Yayasan</span><strong class="text-xl font-bold tabular-nums text-amber-800">{{ number_format($foundationAdminCount, 0, ',', '.') }}</strong></div>
        </div>
        <div class="mt-5 rounded-xl bg-[#f1f8f5] p-4"><p class="text-sm font-semibold text-teal-950">Statistik terhubung ke database</p><p class="mt-1 text-sm leading-6 text-teal-900/80">Angka diperbarui setiap kali halaman ringkasan dibuka.</p></div>
    </article>
</section>

@unless ($isFoundationAdmin)
    <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" data-reveal>
        <div class="flex flex-wrap items-end justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[0.12em] text-violet-700">Direktori terbaru</p><h2 class="mt-1.5 text-xl font-bold text-slate-950">Akun baru ditambahkan</h2></div><a class="text-sm font-bold text-teal-800 underline-offset-4 hover:underline" href="{{ route('admin.users') }}">Buka manajemen akun &rarr;</a></div>
        @if ($latestUsers->isEmpty())
            <p class="mt-5 rounded-xl bg-slate-50 p-5 text-sm text-slate-600">Belum ada akun di portal.</p>
        @else
            <div class="mt-5 overflow-x-auto"><table class="w-full min-w-[560px] text-left text-sm"><thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="pb-3 font-semibold">Nama</th><th class="pb-3 font-semibold">Peran</th><th class="pb-3 font-semibold">Identitas masuk</th><th class="pb-3 font-semibold">Dibuat</th></tr></thead><tbody class="divide-y divide-slate-100">@foreach ($latestUsers as $user)<tr class="transition hover:bg-slate-50"><td class="py-3 font-semibold text-slate-900">{{ $user->name }}</td><td class="py-3 text-slate-600">{{ ['guru' => 'Guru', 'siswa' => 'Siswa', 'admin_remote' => 'All Remote', 'admin_yayasan' => 'Yayasan'][$user->role] ?? $user->role }}</td><td class="py-3 text-slate-600">{{ $user->email ?? $user->nisn }}</td><td class="py-3 text-slate-500">{{ $user->created_at?->format('d M Y') }}</td></tr>@endforeach</tbody></table></div>
        @endif
    </section>
@endunless
@endsection
