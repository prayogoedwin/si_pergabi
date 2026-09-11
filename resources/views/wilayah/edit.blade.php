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
        <span class="text-gray-500 dark:text-gray-400">{{ __('Edit') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Edit Wilayah') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Ubah nama wilayah') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <form action="{{ route('wilayah.update', $wilayah) }}" method="POST" class="max-w-md">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('Kode') }}</label>
                    <input type="text" value="{{ $wilayah->kode }}" disabled
                        class="w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-900 border border-gray-300 dark:border-gray-600">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Kode tidak dapat diubah.') }}</p>
                </div>

                <div class="mb-6">
                    <x-forms.input label="Nama" name="nama" type="text" value="{{ old('nama', $wilayah->nama) }}" required maxlength="100" />
                </div>

                <div class="flex gap-3">
                    <x-button type="primary">{{ __('Update') }}</x-button>
                    <a href="{{ route('wilayah.index') }}">
                        <x-button type="secondary">{{ __('Cancel') }}</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
