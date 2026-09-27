@include('kta._qr-client')
<script src="{{ asset('js/html-to-image.js') }}"></script>
<style>
    @media print {
        @page { size: auto; margin: 10mm; }
        .print-id .kta-print-wrap,
        .print-a4 .kta-print-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8mm;
        }
        .print-id .kta-demo,
        .print-a4 .kta-demo {
            width: 85.6mm;
            height: 53.98mm;
            box-shadow: none;
            border-radius: 0;
        }
    }
</style>

@if ($allowPrint ?? true)
<div class="flex flex-wrap gap-2 mb-4 print:hidden">
    <button type="button" onclick="printKta('id')" class="rounded-xl bg-saffron-600 text-white px-4 py-2.5 text-sm font-medium">Cetak ID Card</button>
    <button type="button" onclick="printKta('a4')" class="rounded-xl bg-navy-900 text-cream-100 px-4 py-2.5 text-sm font-medium">Cetak A4 / PDF</button>
    <button type="button" onclick="unduhPng('depan')" class="rounded-xl border border-gold-400/40 bg-white dark:bg-navy-950 px-4 py-2.5 text-sm font-medium" title="Ukuran KTP / SIM 85,60 × 53,98 mm">Unduh PNG depan</button>
    <button type="button" onclick="unduhPng('belakang')" class="rounded-xl border border-gold-400/40 bg-white dark:bg-navy-950 px-4 py-2.5 text-sm font-medium" title="Ukuran KTP / SIM 85,60 × 53,98 mm">Unduh PNG belakang</button>
</div>
@endif

<div id="kta-print-root" class="kta-print-wrap">
    @include('kta._kartu')
</div>

<script>
    const verifikasiUrl = @js($verifikasiUrl);

    function renderKtaQr() {
        return window.drawPergabiQrAll('[data-kta-qr]', verifikasiUrl, 160).then(function () {
            return window.drawPergabiQrFromData('[data-kta-ttd-qr]');
        });
    }

    renderKtaQr();

    window.printKta = function (mode) {
        renderKtaQr().then(function () {
            document.body.classList.remove('print-id', 'print-a4');
            document.body.classList.add(mode === 'a4' ? 'print-a4' : 'print-id');
            window.print();
        });
    };

    const ktaNomor = @js($anggota->nomor_anggota);
    const ktaPngLebar = 1011;
    const ktaPngTinggi = 638;

    function nextFrame() {
        return new Promise(function (resolve) {
            requestAnimationFrame(function () {
                requestAnimationFrame(resolve);
            });
        });
    }

    function waitImages(root) {
        return Promise.all(Array.prototype.map.call(root.querySelectorAll('img'), function (img) {
            if (img.decode) {
                return img.decode().catch(function () {});
            }
            if (img.complete) {
                return Promise.resolve();
            }
            return new Promise(function (resolve) {
                img.addEventListener('load', resolve, { once: true });
                img.addEventListener('error', resolve, { once: true });
            });
        }));
    }

    function replaceCanvasesWithImages(root) {
        const pairs = [];
        root.querySelectorAll('canvas').forEach(function (canvas) {
            const img = document.createElement('img');
            img.src = canvas.toDataURL('image/png');
            img.alt = '';
            img.className = canvas.className;
            const cs = window.getComputedStyle(canvas);
            img.style.width = cs.width;
            img.style.height = cs.height;
            img.style.display = 'block';
            img.style.margin = cs.margin;
            img.style.background = '#ffffff';
            canvas.replaceWith(img);
            pairs.push({ canvas: canvas, img: img });
        });
        return function restore() {
            pairs.forEach(function (pair) {
                if (pair.img.parentNode) {
                    pair.img.replaceWith(pair.canvas);
                }
            });
        };
    }

    function unduhDataUrl(dataUrl, nama) {
        const image = new Image();
        image.onload = function () {
            const out = document.createElement('canvas');
            out.width = ktaPngLebar;
            out.height = ktaPngTinggi;
            const ctx = out.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, ktaPngLebar, ktaPngTinggi);
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage(image, 0, 0, ktaPngLebar, ktaPngTinggi);
            const link = document.createElement('a');
            link.download = nama;
            link.href = out.toDataURL('image/png');
            link.click();
        };
        image.src = dataUrl;
    }

    window.unduhPng = async function (sisi) {
        const card = document.getElementById(sisi === 'belakang' ? 'kta-back' : 'kta-front');
        const wrap = document.getElementById('kta-print-root');
        if (! card || ! wrap || ! window.htmlToImage || ! window.htmlToImage.toPng) {
            return;
        }

        wrap.classList.add('kta-exporting');
        card.scrollIntoView({ block: 'center', inline: 'center' });

        const prevWidth = card.style.width;
        const prevHeight = card.style.height;
        let restoreCanvases = function () {};

        try {
            await renderKtaQr();
            if (document.fonts && document.fonts.ready) {
                await document.fonts.ready;
            }
            await waitImages(card);
            await nextFrame();

            card.style.width = card.offsetWidth + 'px';
            card.style.height = card.offsetHeight + 'px';
            restoreCanvases = replaceCanvasesWithImages(card);
            await waitImages(card);
            await nextFrame();

            const dataUrl = await window.htmlToImage.toPng(card, {
                pixelRatio: 1,
                canvasWidth: ktaPngLebar,
                canvasHeight: ktaPngTinggi,
                backgroundColor: '#ffffff',
                cacheBust: false,
                skipFonts: true,
                skipAutoScale: true,
            });

            unduhDataUrl(dataUrl, 'kta-' + ktaNomor + '-' + (sisi === 'belakang' ? 'belakang' : 'depan') + '.png');
        } finally {
            restoreCanvases();
            card.style.width = prevWidth;
            card.style.height = prevHeight;
            wrap.classList.remove('kta-exporting');
        }
    };
</script>
