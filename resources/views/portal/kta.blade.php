<x-layouts.portal title="Kartu digital" :wide="true">
    <x-slot name="head">
        {{-- Skrip dan gaya cetak di kta._cetak --}}
    </x-slot>

    <div class="mb-4">
        <h1 class="font-display text-3xl text-navy-900">Kartu digital</h1>
        <p class="text-sm text-navy-800/70 mt-1">
            @if ($unlocked)
                Kartu tanda anggota PERGABI. Cetak atau unduh untuk keperluan organisasi.
            @else
                Kartu digital dan cetak terbuka setelah pendaftaran disetujui Pengurus Pusat.
            @endif
        </p>
    </div>

    @if (! $unlocked)
        <div class="rounded-2xl border border-gold-400/30 bg-white p-6 text-center">
            <p class="font-semibold">Belum dapat ditampilkan</p>
            <p class="text-sm text-navy-800/70 mt-2">Status saat ini: {{ $anggota->statusLabel() }}</p>
            <p class="text-sm text-navy-800/60 mt-1">Nomor anggota dan KTA terbit otomatis setelah persetujuan PP.</p>
        </div>
    @else
        @include('kta._cetak', ['fotoUrl' => $fotoUrl])
    @endif
</x-layouts.portal>
