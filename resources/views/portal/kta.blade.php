<x-layouts.portal title="Kartu digital" :wide="true">
    <x-slot name="head">
        {{-- Skrip dan gaya cetak di kta._cetak --}}
    </x-slot>

    <div class="mb-4">
        <h1 class="font-display text-3xl text-navy-900">Kartu digital</h1>
        <p class="text-sm text-navy-800/70 mt-1">
            @if ($unlocked)
                Kartu tanda anggota PERGABI. Cetak atau unduh untuk keperluan organisasi.
            @elseif ($hasKartu)
                Kartu ini tidak aktif. Ajukan verifikasi ulang agar keanggotaan diproses kembali.
            @else
                Kartu digital dan cetak terbuka setelah pendaftaran disetujui Pengurus Pusat.
            @endif
        </p>
    </div>

    @if (! $hasKartu)
        <div class="rounded-2xl border border-gold-400/30 bg-white p-6 text-center">
            <p class="font-semibold">Belum dapat ditampilkan</p>
            <p class="text-sm text-navy-800/70 mt-2">Status saat ini: {{ $anggota->statusLabel() }}</p>
            <p class="text-sm text-navy-800/60 mt-1">Nomor anggota dan KTA terbit otomatis setelah persetujuan PP.</p>
        </div>
    @else
        @unless ($unlocked)
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <p class="font-semibold">Tidak aktif</p>
                <p class="mt-1">Kartu tanda anggota tidak berlaku sampai pengajuan ulang disetujui Pengurus Pusat.</p>
                @if ($anggota->canRenew())
                    <a href="{{ route('portal.perpanjang') }}" class="mt-3 inline-flex rounded-xl bg-saffron-600 text-white px-4 py-2 text-sm font-semibold">Pengajuan / verifikasi ulang</a>
                @endif
            </div>
        @endunless
        @include('kta._cetak', ['fotoUrl' => $fotoUrl, 'allowPrint' => $unlocked])
    @endif
</x-layouts.portal>
