<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Riwayat status</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Setiap perubahan status tercatat beserta alasan dan petugasnya.</p>
    </div>
    <ol class="divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($anggota->statusLogs as $log)
            <li class="px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $log->statusDariLabel() }}
                            <span class="text-gray-400">→</span>
                            {{ $log->statusKeLabel() }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $log->alasan ?: '—' }}</p>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $log->user?->name ?? 'Sistem' }}
                        </p>
                    </div>
                    <time class="text-xs text-gray-500 whitespace-nowrap">{{ $log->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</time>
                </div>
            </li>
        @empty
            <li class="px-6 py-4 text-sm text-gray-500">Belum ada riwayat.</li>
        @endforelse
    </ol>
</div>
