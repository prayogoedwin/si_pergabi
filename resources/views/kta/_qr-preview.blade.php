@props([
    'verifikasiUrl',
    'nomorAnggota',
    'size' => 240,
])

<div class="rounded-2xl bg-white border border-gold-400/25 p-5 text-center">
    <p class="text-sm font-semibold text-navy-900 dark:text-cream-100">QR Code anggota</p>
    <p class="text-xs text-navy-800/60 dark:text-cream-100/60 mt-1">Dibuat di browser saat halaman dibuka. Tidak disimpan sebagai gambar di server.</p>

    <div class="mt-4 mx-auto bg-white p-3 rounded-xl inline-block border border-navy-900/10">
        <canvas data-anggota-qr width="{{ $size }}" height="{{ $size }}" class="block"></canvas>
    </div>

    <p class="mt-4 text-sm font-semibold tracking-wide">{{ $nomorAnggota }}</p>
    <p class="mt-2 text-xs break-all text-navy-800/60 dark:text-cream-100/65">{{ $verifikasiUrl }}</p>

    <div class="mt-4 flex flex-wrap justify-center gap-2">
        <a href="{{ $verifikasiUrl }}" target="_blank" rel="noopener"
            class="rounded-xl bg-saffron-600 text-white px-4 py-2.5 text-sm font-medium">Buka halaman verifikasi</a>
        <button type="button" onclick="unduhQrAnggota()"
            class="rounded-xl border border-gold-400/40 bg-white dark:bg-navy-950 px-4 py-2.5 text-sm font-medium">Unduh QR</button>
    </div>
</div>

@include('kta._qr-client')
<script>
    const verifikasiQrUrl = @js($verifikasiUrl);
    const qrSize = {{ (int) $size }};

    window.drawPergabiQrAll('[data-anggota-qr]', verifikasiQrUrl, qrSize);

    window.unduhQrAnggota = function () {
        window.whenPergabiQrReady(function () {
            const canvas = document.querySelector('[data-anggota-qr]');
            if (! canvas) {
                return;
            }
            window.drawPergabiQr(canvas, verifikasiQrUrl, qrSize).then(function () {
                const link = document.createElement('a');
                link.download = @js('qr-'.$nomorAnggota.'.png');
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        });
    };
</script>
