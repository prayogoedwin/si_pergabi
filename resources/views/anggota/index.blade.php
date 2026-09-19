<x-layouts.app>
    <div class="mb-6">
        <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Anggota</h1>
        <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">Pendaftaran dan verifikasi keanggotaan · {{ $wilayahLabel }}</p>
    </div>

    <form method="GET" class="mb-6 rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 p-4">
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
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">NIK</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">PD / PC</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e4ddd3] dark:divide-gold-400/15">
                @forelse ($anggota as $row)
                    <tr>
                        <td class="px-4 py-3 text-sm text-navy-900 dark:text-cream-100">{{ $row->namaLengkap() }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800/70 dark:text-cream-100/70">{{ $row->nik }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800/70 dark:text-cream-100/70">{{ $row->pd?->nama }} / {{ $row->pc?->nama }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800 dark:text-cream-100">{{ $row->statusLabel() }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('anggota.show', $row) }}" class="text-sm text-navy-800 hover:underline dark:text-gold-400">Detail</a>
                            <a href="{{ route('anggota.qr', $row) }}" class="ml-3 text-sm text-navy-800 hover:underline dark:text-gold-400">QR</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-sm text-navy-800/50 dark:text-cream-100/50">Belum ada pendaftaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $anggota->links() }}</div>
</x-layouts.app>
