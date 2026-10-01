<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('link-informasi.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Link Informasi') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Lihat') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Detail Link Informasi') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Data tautan untuk portal anggota.') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-link-informasi'))
                <a href="{{ route('link-informasi.edit', $link) }}">
                    <x-button type="primary">{{ __('Ubah') }}</x-button>
                </a>
            @endif
            <a href="{{ route('link-informasi.index') }}">
                <x-button type="secondary">{{ __('Kembali') }}</x-button>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6 max-w-2xl space-y-6">
            <div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Nama') }}</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $link->nama }}</div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('URL') }}</div>
                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:underline break-all">{{ $link->url }}</a>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Keterangan') }}</div>
                <div class="text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $link->keterangan ?: '—' }}</div>
            </div>
            <div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Status') }}</div>
                @if($link->isAktif())
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Tidak Aktif</span>
                @endif
            </div>
            <div>
                <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Dibuat') }}</div>
                <div class="text-gray-900 dark:text-gray-100">{{ $link->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>
</x-layouts.app>
