<x-layouts.app>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Laporan</h1>
            <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">Rekap keanggotaan tabular · {{ $wilayahLabel }}</p>
        </div>
        @if (auth()->user()->hasPermission('download-laporan') || auth()->user()->isSuperAdmin())
            <a href="{{ route('laporan.export', $filterQuery) }}">
                <x-button type="secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download Excel
                </x-button>
            </a>
        @endif
    </div>

    @include('laporan._filters', [
        'filterAction' => route('laporan.index'),
        'resetHref' => route('laporan.index'),
    ])

    <div class="space-y-6">
        @foreach ($sections as $section)
            <section class="bg-white dark:bg-navy-900 rounded-lg border border-[#e4ddd3] dark:border-gold-400/25 overflow-x-auto">
                <h2 class="px-4 py-3 text-sm font-semibold text-navy-900 dark:text-cream-100 border-b border-[#e4ddd3] dark:border-gold-400/20">{{ $section['judul'] }}</h2>
                <table class="min-w-full divide-y divide-[#e4ddd3] dark:divide-gold-400/20">
                    <thead class="bg-[#f7f3ee] dark:bg-navy-950">
                        <tr>
                            @foreach ($section['headings'] as $heading)
                                <th class="px-4 py-3 text-left text-xs font-medium text-navy-800/50 dark:text-gold-400 uppercase">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e4ddd3] dark:divide-gold-400/15">
                        @forelse ($section['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td class="px-4 py-3 text-sm text-navy-900 dark:text-cream-100">{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($section['headings']) }}" class="px-4 py-6 text-sm text-navy-800/50 dark:text-cream-100/50">Belum ada data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @endforeach
    </div>
</x-layouts.app>
