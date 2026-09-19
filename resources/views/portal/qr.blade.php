<x-layouts.portal title="QR Code">
    <div class="mb-4">
        <h1 class="font-display text-3xl text-navy-900">QR Code anggota</h1>
        <p class="text-sm text-navy-800/70 mt-1">
            @if ($unlocked)
                Pindai QR ini untuk membuka halaman verifikasi kartu. QR yang sama tercetak di kartu tanda anggota.
            @else
                QR Code tersedia setelah nomor anggota terbit.
            @endif
        </p>
    </div>

    @if (! $unlocked)
        <div class="rounded-2xl border border-gold-400/30 bg-white p-6 text-center">
            <p class="font-semibold">Belum dapat ditampilkan</p>
            <p class="text-sm text-navy-800/70 mt-2">Status saat ini: {{ $anggota->statusLabel() }}</p>
            <p class="text-sm text-navy-800/60 mt-1">Nomor anggota terbit otomatis setelah persetujuan PP.</p>
        </div>
    @else
        @include('kta._qr-preview', [
            'verifikasiUrl' => $verifikasiUrl,
            'nomorAnggota' => $anggota->nomor_anggota,
        ])
    @endif
</x-layouts.portal>
