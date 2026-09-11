<x-layouts.app>
    <div class="mb-6">
        <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Cache</h1>
        <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">Bersihkan cache per kunci, per prefix, atau seluruhnya.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 p-5">
            <p class="text-sm font-semibold text-navy-900 dark:text-cream-100 mb-1">Clear all</p>
            <p class="text-xs text-navy-800/50 dark:text-cream-100/60 mb-4">Hapus seluruh cache aplikasi, termasuk dashboard dan pengaturan.</p>
            <form method="POST" action="{{ route('cache.flush') }}" onsubmit="return confirm('Hapus semua cache?')">
                @csrf
                <button type="submit" class="inline-flex w-full h-[42px] items-center justify-center gap-2 rounded-lg bg-red-700 text-white text-sm font-medium hover:bg-red-800 transition-colors">
                    <i class="fa-solid fa-trash text-xs"></i>
                    Clear all
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-xl bg-white dark:bg-navy-900 border border-[#e4ddd3] dark:border-gold-400/25 p-5">
            <p class="text-sm font-semibold text-navy-900 dark:text-cream-100 mb-1">Clear per prefix</p>
            <p class="text-xs text-navy-800/50 dark:text-cream-100/60 mb-4">Dashboard nasional, provinsi, dan kabupaten/kota disimpan terpisah.</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($prefixes as $group)
                    <form method="POST" action="{{ route('cache.destroy-prefix') }}">
                        @csrf
                        <input type="hidden" name="prefix" value="{{ $group['prefix'] }}">
                        <button type="submit" class="inline-flex h-[42px] items-center gap-2 rounded-lg border border-[#e4ddd3] bg-[#f7f3ee] px-3 text-sm font-medium text-navy-800 hover:border-navy-800/40 dark:bg-navy-950 dark:text-gold-400 dark:border-gold-400/30 dark:hover:border-gold-400 transition-colors">
                            <i class="fa-solid fa-layer-group text-xs"></i>
                            {{ $group['label'] }}
                            <span class="text-[11px] opacity-60">{{ $group['prefix'] }}</span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-navy-900 rounded-lg border border-[#e4ddd3] dark:border-gold-400/25 overflow-x-auto">
        <table class="min-w-full divide-y divide-[#e4ddd3] dark:divide-gold-400/20">
            <thead class="bg-[#f7f3ee] dark:bg-navy-950">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Kunci</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Grup</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Wilayah</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#e4ddd3] dark:divide-gold-400/15">
                @forelse ($entries as $entry)
                    <tr>
                        <td class="px-4 py-3 text-sm font-mono text-navy-900 dark:text-cream-100">{{ $entry['key'] }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800/70 dark:text-cream-100/70">{{ $entry['group'] }}</td>
                        <td class="px-4 py-3 text-sm text-navy-800/70 dark:text-cream-100/70">{{ $entry['wilayah'] }}</td>
                        <td class="px-4 py-3 text-sm">
                            @if ($entry['exists'])
                                <span class="text-emerald-700 dark:text-gold-400">Ada</span>
                            @else
                                <span class="text-navy-800/40 dark:text-cream-100/40">Kosong</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <form method="POST" action="{{ route('cache.destroy') }}">
                                @csrf
                                <input type="hidden" name="key" value="{{ $entry['key'] }}">
                                <button type="submit" class="text-sm text-navy-800 hover:underline dark:text-gold-400">Clear</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-sm text-navy-800/50 dark:text-cream-100/50">Belum ada cache yang tercatat. Buka dashboard untuk mengisi cache nasional, provinsi, atau kabupaten/kota.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
