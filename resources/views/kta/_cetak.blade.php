@include('kta._qr-client')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<style>
    @media print {
        @page { size: auto; margin: 10mm; }
        .print-id .kta-print-wrap,
        .print-a4 .kta-print-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10mm;
        }
        .print-id .kta-demo { width: 170mm; box-shadow: none; border-radius: 0; }
        .print-a4 .kta-print-wrap { padding-top: 8mm; gap: 14mm; }
        .print-a4 .kta-demo { width: 250mm; max-width: 100%; box-shadow: none; border-radius: 0; }
    }
</style>

<div class="flex flex-wrap gap-2 mb-4 print:hidden">
    <button type="button" onclick="printKta('id')" class="rounded-xl bg-saffron-600 text-white px-4 py-2.5 text-sm font-medium">Cetak ID Card</button>
    <button type="button" onclick="printKta('a4')" class="rounded-xl bg-navy-900 text-cream-100 px-4 py-2.5 text-sm font-medium">Cetak A4 / PDF</button>
    <button type="button" onclick="unduhPng()" class="rounded-xl border border-gold-400/40 bg-white dark:bg-navy-950 px-4 py-2.5 text-sm font-medium">Unduh PNG</button>
</div>

<div id="kta-print-root" class="kta-print-wrap flex flex-col gap-4">
    @include('kta._kartu')
</div>

<script>
    const verifikasiUrl = @js($verifikasiUrl);

    function renderKtaQr() {
        return window.drawPergabiQrAll('[data-kta-qr]', verifikasiUrl, 160);
    }

    renderKtaQr();

    window.printKta = function (mode) {
        renderKtaQr().then(function () {
            document.body.classList.remove('print-id', 'print-a4');
            document.body.classList.add(mode === 'a4' ? 'print-a4' : 'print-id');
            window.print();
        });
    };

    window.unduhPng = async function () {
        const card = document.getElementById('kta-front');
        if (! card || ! window.html2canvas) {
            return;
        }
        await renderKtaQr();
        const canvas = await html2canvas(card, { scale: 3, backgroundColor: '#ffffff', useCORS: true });
        const link = document.createElement('a');
        link.download = @js('kta-'.$anggota->nomor_anggota.'.png');
        link.href = canvas.toDataURL('image/png');
        link.click();
    };
</script>
