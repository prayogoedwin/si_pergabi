<x-layouts.app>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Anggota</h1>
            <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">Pendaftaran dan verifikasi keanggotaan · {{ $wilayahLabel }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('anggota.export', request()->only(['nama', 'status', 'provinsi', 'kabupaten'])) }}">
                <x-button type="secondary" tag="span">Export Excel</x-button>
            </a>
            @if (auth()->user()?->isSuperAdmin() || auth()->user()?->hasPermission('import-anggota'))
                <a href="{{ route('anggota.import') }}">
                    <x-button type="secondary" tag="span">Import Excel</x-button>
                </a>
            @endif
        </div>
    </div>

    <form method="GET" class="mb-6 rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label for="cari-nama" class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">Nama</label>
                <div class="flex gap-2">
                    <input id="cari-nama" type="search" name="nama" value="{{ request('nama') }}" placeholder="Ketik nama anggota..."
                        class="min-w-0 flex-1 h-[42px] rounded-lg border border-[#d6cfc3] bg-white px-3 text-sm text-navy-900 placeholder:text-navy-800/40 focus:border-navy-800 focus:outline-none focus:ring-2 focus:ring-navy-800/15 dark:border-gold-400/30 dark:bg-navy-950 dark:text-cream-100 dark:placeholder:text-cream-100/40 dark:focus:border-gold-400">
                    <button type="submit" class="inline-flex h-[42px] shrink-0 items-center rounded-lg bg-navy-800 px-3 text-sm font-medium text-white hover:bg-navy-900 dark:bg-gold-400 dark:text-navy-950 dark:hover:bg-gold-300">Cari</button>
                </div>
            </div>

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
                <label class="block text-xs text-navy-800/50 dark:text-cream-100/60 mb-1">Status</label>
                <select name="status" class="w-full" data-placeholder="Semua status" data-search="off" data-autosubmit="true">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    <div class="bg-white dark:bg-navy-900 rounded-lg border border-[#e4ddd3] dark:border-gold-400/25 overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e4ddd3] dark:divide-gold-400/20">
            <thead class="bg-[#f7f3ee] dark:bg-navy-950">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">No. Anggota</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">NIK</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">PD / PC</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Masa berlaku</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e4ddd3] dark:divide-gold-400/15">
                @forelse ($anggota as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-navy-900 dark:text-cream-100">{{ $row->namaLengkap() }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800/70 dark:text-cream-100/70 whitespace-nowrap">{{ $row->nomor_anggota ?: '—' }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800/70 dark:text-cream-100/70">{{ $row->nik }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800/70 dark:text-cream-100/70">{{ $row->pd?->nama }} / {{ $row->pc?->nama }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800 dark:text-cream-100">
                            <span>{{ $row->masa_berlaku_hingga?->format('d M Y') ?: '—' }}</span>
                            @if ($row->isMasaBerlakuHabis())
                                <span class="block text-xs font-medium text-red-600">Habis</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm {{ $row->isAktif() ? 'text-navy-800 dark:text-cream-100' : 'text-red-700 dark:text-red-400' }}">{{ $row->statusLabel() }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('anggota.show', $row) }}" class="text-sm text-navy-800 hover:underline dark:text-gold-400">Detail</a>
                            <a href="{{ route('anggota.qr', $row) }}" class="ml-3 text-sm text-navy-800 hover:underline dark:text-gold-400">QR</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-sm text-navy-800/50 dark:text-cream-100/50">Belum ada pendaftaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $anggota->links() }}</div>
</x-layouts.app>
