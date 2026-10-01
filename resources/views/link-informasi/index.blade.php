<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Link Informasi') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Link Informasi') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Tautan yang tampil di menu Informasi portal anggota jika status aktif.') }}</p>
        </div>
        @if(auth()->user()->hasPermission('create-link-informasi'))
            <a href="{{ route('link-informasi.create') }}">
                <x-button type="primary">{{ __('Tambah Link') }}</x-button>
            </a>
        @endif
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Nama') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('URL') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Keterangan') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($links as $link)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $link->nama }}</td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 hover:underline break-all">{{ $link->url }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $link->keterangan ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($link->isAktif())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Aktif</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Tidak Aktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                @if(auth()->user()->hasPermission('show-link-informasi'))
                                    <a href="{{ route('link-informasi.show', $link) }}" class="text-green-600 dark:text-green-400 hover:underline mr-3">Lihat</a>
                                @endif
                                @if(auth()->user()->hasPermission('edit-link-informasi'))
                                    <a href="{{ route('link-informasi.edit', $link) }}" class="text-blue-600 dark:text-blue-400 hover:underline mr-3">Ubah</a>
                                @endif
                                @if(auth()->user()->hasPermission('delete-link-informasi'))
                                    <form action="{{ route('link-informasi.destroy', $link) }}" method="POST" class="inline" onsubmit="return confirm('Hapus link informasi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Belum ada link informasi.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($links->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $links->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
