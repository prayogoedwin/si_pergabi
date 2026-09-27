<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('anggota.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Anggota</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('anggota.show', $anggota) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $anggota->namaLengkap() }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-500">QR Code</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">QR Code {{ $anggota->namaLengkap() }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $anggota->statusLabel() }}</p>
    </div>

    @if (! $unlocked)
        <div class="rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-6">
            <p class="font-semibold">QR Code belum tersedia</p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Nomor anggota terbit setelah persetujuan Pengurus Pusat. Status saat ini: {{ $anggota->statusLabel() }}.</p>
        </div>
    @else
        @unless ($anggota->isAktif())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Tidak aktif</p>
                <p class="mt-1">Halaman verifikasi publik akan menampilkan status tidak aktif.</p>
            </div>
        @endunless
        <div class="max-w-lg">
            @include('kta._qr-preview', [
                'verifikasiUrl' => $verifikasiUrl,
                'nomorAnggota' => $anggota->nomor_anggota,
            ])
        </div>
    @endif
</x-layouts.app>
