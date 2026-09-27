<x-layouts.app>
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="font-display text-3xl font-bold text-navy-900 dark:text-cream-100">Laporan Visual</h1>
            <p class="text-navy-800/60 dark:text-cream-100/70 mt-1">Rekap keanggotaan dalam grafik · {{ $wilayahLabel }}</p>
        </div>
        <a href="{{ route('laporan.index', $filterQuery) }}" class="inline-flex items-center rounded-lg border border-[#e4ddd3] bg-white px-4 py-2 text-sm font-medium text-navy-800 hover:border-navy-800/40 dark:bg-navy-900 dark:text-gold-400 dark:border-gold-400/30">
            Lihat tabel
        </a>
    </div>

    @include('laporan._filters', [
        'filterAction' => route('laporan.visual'),
        'resetHref' => route('laporan.visual'),
    ])

    @php
        $statCards = collect($charts)->where('type', 'card')->values();
        $grafik = collect($charts)->where('type', '!=', 'card')->values();
    @endphp

    @if ($statCards->isNotEmpty())
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach ($statCards as $chart)
                <div id="laporan-card-{{ $chart['id'] }}" class="bg-white dark:bg-navy-900 rounded-lg p-5 border border-[#e4ddd3] dark:border-gold-400/30 border-l-4 border-l-navy-800 dark:border-l-gold-400">
                    <p class="text-sm font-medium text-navy-800/50 dark:text-gold-400">{{ $chart['judul'] }}</p>
                    <p class="text-3xl font-bold text-navy-900 dark:text-cream-100 mt-1">{{ $chart['data'][0] ?? 0 }}</p>
                    @if (! empty($chart['categories'][0]))
                        <p class="text-xs text-navy-800/45 dark:text-cream-100/60 mt-2">{{ $chart['categories'][0] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        @foreach ($grafik as $chart)
            <section @class([
                'bg-white dark:bg-navy-900 rounded-lg border border-[#e4ddd3] dark:border-gold-400/25 overflow-hidden',
                'xl:col-span-2' => in_array($chart['type'], ['bar', 'line'], true),
            ])>
                <h2 class="px-4 py-3 text-sm font-semibold text-navy-900 dark:text-cream-100 border-b border-[#e4ddd3] dark:border-gold-400/20">{{ $chart['judul'] }}</h2>
                @if ($chart['empty'])
                    <p class="px-4 py-10 text-sm text-navy-800/50 dark:text-cream-100/50">Belum ada data.</p>
                @else
                    <div id="laporan-chart-{{ $chart['id'] }}" class="px-2 py-3"></div>
                @endif
            </section>
        @endforeach
    </div>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const charts = @json($charts);
            const dark = document.documentElement.classList.contains('dark');
            const text = dark ? '#efe8dc' : '#0c2244';
            const muted = dark ? 'rgba(239, 232, 220, 0.55)' : 'rgba(12, 34, 68, 0.45)';
            const grid = dark ? 'rgba(240, 193, 75, 0.12)' : 'rgba(12, 34, 68, 0.08)';

            Highcharts.setOptions({
                credits: { enabled: false },
                colors: ['#0c2244', '#ee6b24', '#f0c14b', '#c94b10', '#1a2b40', '#e8b42e', '#071422'],
                chart: { backgroundColor: 'transparent', style: { fontFamily: 'Be Vietnam Pro, sans-serif' } },
                title: { text: null },
                legend: { itemStyle: { color: text } },
            });

            charts.forEach(function (chart) {
                if (chart.empty || chart.type === 'card') {
                    return;
                }

                const options = {
                    chart: {
                        type: chart.type,
                        height: chart.type === 'bar' ? Math.max(280, chart.categories.length * 28) : 320,
                    },
                    tooltip: { pointFormat: '{series.name}: <b>{point.y}</b>' },
                };

                if (chart.type === 'pie') {
                    options.series = [{ name: 'Jumlah', data: chart.pie }];
                    options.tooltip = { pointFormat: '{series.name}: <b>{point.y}</b> ({point.percentage:.1f}%)' };
                    options.plotOptions = {
                        pie: {
                            allowPointSelect: true,
                            cursor: 'pointer',
                            dataLabels: { enabled: true, format: '{point.name}: {point.y}', style: { color: text, textOutline: 'none' } },
                        },
                    };
                } else {
                    options.xAxis = {
                        categories: chart.categories,
                        labels: { style: { color: text } },
                        lineColor: grid,
                    };
                    options.yAxis = {
                        title: { text: 'Jumlah', style: { color: muted } },
                        labels: { style: { color: muted } },
                        gridLineColor: grid,
                        min: 0,
                        allowDecimals: false,
                    };
                    options.series = [{
                        name: 'Jumlah',
                        data: chart.data,
                        color: chart.type === 'line' ? '#ee6b24' : '#0c2244',
                    }];
                    options.plotOptions = {
                        column: { borderRadius: 4, color: '#ee6b24' },
                        bar: { borderRadius: 4, color: '#0c2244' },
                        line: { marker: { radius: 3 } },
                    };
                }

                Highcharts.chart('laporan-chart-' + chart.id, options);
            });
        });
    </script>
</x-layouts.app>
