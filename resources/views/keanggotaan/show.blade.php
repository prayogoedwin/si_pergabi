<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Keanggotaan saya</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Pantau status pendaftaran dan riwayat persetujuan.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <p class="text-sm text-gray-500">Nama</p>
            <p class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $anggota->namaLengkap() }}</p>
            <p class="text-sm text-gray-600 mt-4">NIK {{ $anggota->nik }}</p>
            <p class="text-sm text-gray-600">{{ $anggota->email }}</p>
            <p class="text-sm text-gray-600 mt-2">PD {{ $anggota->pd?->nama ?? $anggota->pd_kode }} · PC {{ $anggota->pc?->nama ?? $anggota->pc_kode }}</p>
            @if ($anggota->nomor_anggota)
                <p class="mt-4 text-lg font-semibold text-saffron-700">Nomor anggota {{ $anggota->nomor_anggota }}</p>
            @endif
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <p class="text-sm text-gray-500">Status saat ini</p>
            <p class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $anggota->statusLabel() }}</p>
            @if ($anggota->masa_berlaku_hingga)
                <p class="text-sm text-gray-600 mt-3">Masa berlaku s.d. {{ $anggota->masa_berlaku_hingga->format('d M Y') }}</p>
            @endif
        </div>
    </div>

    @include('anggota._status-log')
</x-layouts.app>
