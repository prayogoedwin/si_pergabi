<form method="GET" action="{{ $filterAction }}" class="mb-6 rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 p-4">
    <p class="text-sm font-semibold text-navy-900 dark:text-cream-100 mb-3">Filter</p>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        @if ($showProvinsiFilter)
            <div>
                <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">Provinsi (PD)</label>
                <select name="provinsi" class="w-full" data-placeholder="Ketik nama provinsi..." data-autosubmit="true" data-allow-clear="true">
                    <option value="">Semua provinsi</option>
                    @foreach ($provinsiOptions as $item)
                        <option value="{{ $item->kode }}" @selected($filterProvinsi === $item->kode)>{{ $item->nama }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if ($showKabupatenFilter)
            <div>
                <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">Kabupaten/Kota (PC)</label>
                <select name="kabupaten" class="w-full" data-placeholder="Ketik nama kabupaten/kota..." data-autosubmit="true" data-allow-clear="true" @disabled($showProvinsiFilter && ! $filterProvinsi)>
                    <option value="">Semua kabupaten/kota</option>
                    @foreach ($kabupatenOptions as $item)
                        <option value="{{ $item->kode }}" @selected($filterKabupaten === $item->kode)>{{ $item->nama }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div>
            <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">Tahun rekap</label>
            <select name="tahun" class="w-full" data-search="off" data-autosubmit="true">
                @foreach ($tahunOptions as $tahun)
                    <option value="{{ $tahun }}" @selected($filterTahun === $tahun)>{{ $tahun }}</option>
                @endforeach
            </select>
        </div>

        @if ($showProvinsiFilter)
            <div>
                <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">&nbsp;</label>
                <a href="{{ $resetHref }}"
                    class="inline-flex w-full h-[42px] items-center justify-center gap-2 rounded-lg border border-[#e4ddd3] bg-[#f7f3ee] text-sm font-medium text-navy-800 hover:bg-white hover:border-navy-800/40 dark:bg-navy-950 dark:text-gold-400 dark:border-gold-400/30 dark:hover:bg-navy-800 dark:hover:border-gold-400 transition-colors">
                    Reset nasional
                </a>
            </div>
        @endif
    </div>
</form>
