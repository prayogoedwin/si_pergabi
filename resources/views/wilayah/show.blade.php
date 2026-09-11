<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Master') }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('wilayah.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Wilayah') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('View') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('View Wilayah') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Detail data wilayah') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-wilayah'))
                <a href="{{ route('wilayah.edit', $wilayah) }}">
                    <x-button type="primary">{{ __('Edit Wilayah') }}</x-button>
                </a>
            @endif
            <a href="{{ route('wilayah.index') }}">
                <x-button type="secondary">{{ __('Back') }}</x-button>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <div class="max-w-2xl">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Kode') }}
                    </label>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $wilayah->kode }}
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Nama') }}
                    </label>
                    <div class="text-gray-900 dark:text-gray-100">
                        {{ $wilayah->nama }}
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Tingkat') }}
                    </label>
                    <div>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            {{ $wilayah->tingkatLabel() }}
                        </span>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Wilayah Induk') }}
                    </label>
                    <div class="text-gray-900 dark:text-gray-100">
                        @if($parent)
                            <a href="{{ route('wilayah.show', $parent) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $parent->kode }} — {{ $parent->nama }}
                            </a>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('Tidak ada (tingkat provinsi)') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
