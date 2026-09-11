<x-layouts.app>
    <div class="mb-6">
        <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Dashboard</h1>
        <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">
            Sistem Informasi Keanggotaan PERGABI
            <span class="text-navy-800/40 dark:text-gold-400">· {{ $wilayahLabel }}</span>
        </p>
    </div>

    @if ($canViewAnggota && $nasional)
        <form method="GET" action="{{ route('dashboard') }}" class="mb-6 rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 p-4">
            <p class="text-sm font-semibold text-navy-900 dark:text-cream-100 mb-3">Lihat per wilayah</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">Provinsi (PD)</label>
                    <select name="provinsi" class="w-full" data-placeholder="Ketik nama provinsi..." data-autosubmit="true" data-allow-clear="true">
                        <option value="">Semua provinsi</option>
                        @foreach ($provinsiOptions as $item)
                            <option value="{{ $item->kode }}" @selected($filterProvinsi === $item->kode)>{{ $item->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">Kabupaten/Kota (PC)</label>
                    <select name="kabupaten" class="w-full" data-placeholder="Ketik nama kabupaten/kota..." data-autosubmit="true" data-allow-clear="true" @disabled(! $filterProvinsi)>
                        <option value="">Semua kabupaten/kota</option>
                        @foreach ($kabupatenOptions as $item)
                            <option value="{{ $item->kode }}" @selected($filterKabupaten === $item->kode)>{{ $item->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">&nbsp;</label>
                    <a href="{{ route('dashboard') }}"
                        class="inline-flex w-full h-[42px] items-center justify-center gap-2 rounded-lg border border-[#e4ddd3] bg-[#f7f3ee] text-sm font-medium text-navy-800 hover:bg-white hover:border-navy-800/40 dark:bg-navy-950 dark:text-gold-400 dark:border-gold-400/30 dark:hover:bg-navy-800 dark:hover:border-gold-400 transition-colors">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        Reset nasional
                    </a>
                </div>
            </div>
        </form>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if ($nasional)
            <div class="bg-white dark:bg-navy-900 rounded-lg p-5 border border-[#e4ddd3] dark:border-gold-400/30 border-l-4 border-l-navy-800 dark:border-l-saffron-500">
                <p class="text-sm font-medium text-navy-800/50 dark:text-gold-400">Pengguna</p>
                <p class="text-2xl font-bold text-navy-900 dark:text-cream-100 mt-1">{{ $userCount }}</p>
            </div>
        @endif
        @if ($canViewAnggota)
            <a href="{{ route('anggota.index', $filterQuery) }}" class="bg-white dark:bg-navy-900 rounded-lg p-5 border border-[#e4ddd3] dark:border-gold-400/30 border-l-4 border-l-navy-800 dark:border-l-gold-400 hover:border-navy-800/30 dark:hover:border-gold-400 transition-colors">
                <p class="text-sm font-medium text-navy-800/50 dark:text-gold-400">Total pendaftaran</p>
                <p class="text-2xl font-bold text-navy-900 dark:text-cream-100 mt-1">{{ $totalAnggota }}</p>
                <p class="text-xs text-navy-800/45 dark:text-cream-100/60 mt-2">{{ $wilayahLabel }}</p>
            </a>
            <a href="{{ route('anggota.index', $filterQuery) }}" class="bg-white dark:bg-navy-900 rounded-lg p-5 border border-[#e4ddd3] dark:border-gold-400/30 border-l-4 border-l-navy-800 dark:border-l-saffron-500 hover:border-navy-800/30 dark:hover:border-gold-400 transition-colors">
                <p class="text-sm font-medium text-navy-800/50 dark:text-gold-400">Masih antri</p>
                <p class="text-2xl font-bold text-navy-900 dark:text-cream-100 mt-1">{{ $menungguTotal }}</p>
                <p class="text-xs text-navy-800/45 dark:text-cream-100/60 mt-2">Belum sampai disetujui PP</p>
            </a>
            <a href="{{ route('anggota.index', array_merge($filterQuery, ['status' => \App\Models\Anggota::STATUS_AKTIF])) }}" class="bg-white dark:bg-navy-900 rounded-lg p-5 border border-[#e4ddd3] dark:border-gold-400/30 border-l-4 border-l-navy-800 dark:border-l-gold-400 hover:border-navy-800/30 dark:hover:border-gold-400 transition-colors">
                <p class="text-sm font-medium text-navy-800/50 dark:text-gold-400">Anggota aktif</p>
                <p class="text-2xl font-bold text-navy-900 dark:text-cream-100 mt-1">{{ $statusCounts[\App\Models\Anggota::STATUS_AKTIF] ?? 0 }}</p>
                <p class="text-xs text-navy-800/45 dark:text-cream-100/60 mt-2">Nomor anggota sudah terbit</p>
            </a>
        @endif
    </div>

    @if ($canViewAnggota)
        <h2 class="text-sm font-semibold uppercase tracking-wide text-navy-800/45 dark:text-gold-400 mb-3">Status pendaftaran</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach (\App\Models\Anggota::statusLabels() as $status => $label)
                @php
                    $accents = [
                        \App\Models\Anggota::STATUS_BELUM_VERIFIKASI_EMAIL => 'border-l-navy-800 dark:border-l-gold-400',
                        \App\Models\Anggota::STATUS_MENUNGGU_VERIFIKASI_PC => 'border-l-navy-800 dark:border-l-saffron-500',
                        \App\Models\Anggota::STATUS_MENUNGGU_VALIDASI_PD => 'border-l-navy-800 dark:border-l-saffron-500',
                        \App\Models\Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP => 'border-l-navy-800 dark:border-l-saffron-500',
                        \App\Models\Anggota::STATUS_AKTIF => 'border-l-navy-800 dark:border-l-gold-400',
                        \App\Models\Anggota::STATUS_TIDAK_AKTIF => 'border-l-navy-800/40 dark:border-l-navy-800',
                        \App\Models\Anggota::STATUS_DITOLAK => 'border-l-red-700 dark:border-l-red-500',
                    ];
                @endphp
                <a href="{{ route('anggota.index', array_merge($filterQuery, ['status' => $status])) }}"
                    class="bg-white dark:bg-navy-900 rounded-lg p-4 border border-[#e4ddd3] dark:border-gold-400/25 border-l-4 {{ $accents[$status] ?? 'border-l-navy-800' }} hover:border-navy-800/30 dark:hover:border-gold-400 transition-colors">
                    <p class="text-sm font-medium text-navy-800/55 dark:text-cream-100/70">{{ $label }}</p>
                    <p class="text-2xl font-bold text-navy-900 dark:text-cream-100 mt-1">{{ $statusCounts[$status] ?? 0 }}</p>
                </a>
            @endforeach
        </div>
    @endif
</x-layouts.app>
