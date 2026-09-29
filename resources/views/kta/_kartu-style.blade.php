<style>
    .kta-print-wrap,
    .kta-contoh-wrap {
        --kta-w: 85.6mm;
        --kta-h: 53.98mm;
        --kta-preview-scale: 1;
    }
    @media screen and (min-width: 768px) {
        .kta-print-wrap { --kta-preview-scale: 1.7; }
    }
    @media screen and (min-width: 1100px) {
        .kta-print-wrap { --kta-preview-scale: 1.95; }
    }
    @media screen and (min-width: 900px) {
        .kta-contoh-wrap { --kta-preview-scale: 1.15; }
    }
    .kta-print-wrap {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.75rem;
    }
    .kta-stage {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        height: calc(var(--kta-h) * var(--kta-preview-scale) + 1.6rem);
    }
    .kta-stage-label {
        margin: 0 0 0.4rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        letter-spacing: 0.02em;
    }
    .kta-id1 {
        width: var(--kta-w);
        height: var(--kta-h);
        transform: scale(var(--kta-preview-scale));
        transform-origin: top center;
    }
    .kta-demo {
        position: relative;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        width: var(--kta-w);
        height: var(--kta-h);
        background: #ffffff;
        overflow: hidden;
        border-radius: 2.6mm;
        border: 0.18mm solid #e4ddd3;
        box-shadow: 0 8px 22px rgba(7, 20, 34, 0.14);
        font-family: ui-sans-serif, system-ui, sans-serif;
        color: #111111;
    }
    .kta-demo *,
    .kta-demo *::before,
    .kta-demo *::after { box-sizing: border-box; }
    .kta-demo p { margin: 0; }
    .kta-demo img { pointer-events: none; }
    .kta-ornamen { position: absolute; inset: 0; z-index: 0; pointer-events: none; }
    .kta-ornamen img { image-rendering: auto; }
    .kta-watermark {
        position: absolute;
        left: 50%;
        top: 46%;
        width: 29mm;
        transform: translate(-50%, -50%);
        opacity: 0.07;
        object-fit: contain;
    }
    .kta-demo-back .kta-watermark {
        top: 50%;
        width: 34mm;
        opacity: 0.18;
    }
    .kta-ornamen-kiri-atas {
        position: absolute;
        top: 0;
        left: 0;
        width: 11.92mm;
        height: 12.33mm;
        object-fit: fill;
    }
    .kta-ornamen-kanan-atas {
        position: absolute;
        top: 0;
        right: 0;
        width: 9.05mm;
        height: 7.86mm;
        object-fit: fill;
    }
    .kta-ornamen-kanan-bawah {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 5.72mm;
        height: 9.67mm;
        object-fit: fill;
        z-index: 5;
    }
    .kta-demo-back .kta-ornamen-kanan-bawah { display: none; }
    .kta-lambang { display: none; }
    .kta-demo-back .kta-lambang {
        display: block;
        position: absolute;
        top: 2.3mm;
        right: 10.4mm;
        width: 10.4mm;
        height: 5.6mm;
        object-fit: contain;
        z-index: 2;
        background: #ffffff;
        border-radius: 0.7mm;
        box-shadow: 0 0.12mm 0.3mm rgba(0, 0, 0, 0.08);
    }
    .kta-isi {
        position: relative;
        z-index: 2;
        isolation: isolate;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 1.5mm 4mm 0.7mm;
        background: transparent;
    }
    .kta-isi-belakang {
        padding: 2.3mm 16mm 1.6mm 13.8mm;
    }
    .kta-kop {
        text-align: center;
        color: #0c2244;
        line-height: 1.15;
    }
    .kta-kop-logo {
        height: 10.2mm;
        margin: 0 auto 0.7mm;
        object-fit: contain;
        display: block;
    }
    .kta-kop-nama { font-size: 2.02mm; font-weight: 800; letter-spacing: 0.015em; line-height: 1.2; }
    .kta-kop-singkatan {
        display: block;
        font-size: 2.45mm;
        font-weight: 800;
        letter-spacing: 0.32em;
        margin: 0.2mm 0 0 0.32em;
        line-height: 1.25;
    }
    .kta-kop-alamat { font-size: 1.28mm; margin-top: 0.5mm; font-weight: 600; line-height: 1.25; }
    .kta-judul-depan {
        margin-top: 2.6mm;
        text-align: center;
        color: #111;
        font-size: 2.75mm;
        font-weight: 800;
        letter-spacing: 0.045em;
    }
    .kta-baris-utama {
        display: grid;
        grid-template-columns: 16mm minmax(0, 1fr) 16.2mm;
        gap: 2mm;
        align-items: start;
        margin-top: 2mm;
        flex: 1;
        min-height: 0;
    }
    .kta-foto-wrap { position: relative; width: 16mm; }
    .kta-foto {
        width: 16mm;
        height: 20mm;
        background: #f2f2f2;
        overflow: hidden;
    }
    .kta-foto img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        display: block;
    }
    .kta-foto-label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7mm;
        font-weight: 800;
        color: #5a6b7a;
        letter-spacing: 0.12em;
    }
    .kta-identitas {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        text-align: center;
        padding-top: 1.6mm;
        min-width: 0;
    }
    .kta-nama {
        color: #8b1530;
        font-size: 3.35mm;
        font-weight: 800;
        letter-spacing: 0.03em;
        line-height: 1.08;
        text-transform: uppercase;
        overflow-wrap: anywhere;
    }
    .kta-nomor {
        margin-top: 1.05mm;
        color: #8b1530;
        font-size: 2.65mm;
        font-weight: 800;
        letter-spacing: 0.03em;
    }
    .kta-pd {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }
    .kta-qr {
        width: 16.2mm;
        height: 16.2mm;
        background: #fff;
        padding: 0.25mm;
        align-self: start;
        margin-top: 0.4mm;
    }
    .kta-qr canvas {
        width: 100%;
        height: 100%;
        display: block;
        image-rendering: pixelated;
    }
    .kta-berlaku {
        margin-top: auto;
        padding: 1.1mm 5.6mm 0.4mm 0;
        text-align: right;
        color: #0c2244;
        font-size: 1.48mm;
        font-weight: 700;
        line-height: 1.15;
    }
    .kta-stempel-depan {
        position: absolute;
        right: -7.4mm;
        bottom: 1.1mm;
        width: 12.6mm;
        object-fit: contain;
        z-index: 3;
    }
    .kta-judul-belakang {
        text-align: center;
        color: #8b1530;
        font-size: 2.85mm;
        font-weight: 800;
        letter-spacing: 0.08em;
    }
    .kta-data {
        margin-top: 1.7mm;
        display: grid;
        gap: 0.28mm;
        color: #111;
        font-size: 1.72mm;
        font-weight: 700;
        line-height: 1.4;
    }
    .kta-data-row {
        display: grid;
        grid-template-columns: 18.2mm 1.8mm minmax(0, 1fr);
        align-items: start;
        column-gap: 0.5mm;
    }
    .kta-data-value {
        min-width: 0;
        overflow: hidden;
        line-height: 1.25;
        max-height: 2.5em;
        color: #111111;
    }
    .kta-ttd-blok {
        margin-top: auto;
        padding-top: 1.2mm;
        text-align: center;
    }
    .kta-tanggal {
        font-size: 1.72mm;
        font-weight: 700;
        color: #111;
    }
    .kta-pengurus {
        margin-top: 0.35mm;
        font-size: 1.68mm;
        font-weight: 800;
        color: #111;
    }
    .kta-ttd-area {
        position: relative;
        display: flex;
        align-items: stretch;
        justify-content: center;
        gap: 4mm;
        margin-top: 0.7mm;
        min-height: 11.5mm;
    }
    .kta-ttd-col {
        flex: 1;
        min-width: 0;
        max-width: 28mm;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .kta-ttd-jabatan {
        font-size: 1.62mm;
        font-weight: 700;
        color: #111;
        text-align: center;
        line-height: 1.15;
    }
    .kta-ttd-col canvas.kta-ttd-qr {
        width: 9.2mm;
        height: 9.2mm;
        margin: 0.35mm 0 0.25mm;
        display: block;
        image-rendering: pixelated;
        background: #fff;
    }
    .kta-ttd-nama {
        margin-top: 0.15mm;
        font-size: 1.62mm;
        font-weight: 800;
        color: #111;
        text-align: center;
        line-height: 1.1;
        white-space: nowrap;
    }
    .kta-visi {
        position: relative;
        z-index: 4;
        flex-shrink: 0;
        background: #6b1220;
        color: #fff;
        text-align: center;
        font-size: 1.38mm;
        font-weight: 700;
        line-height: 1.2;
        padding: 0.95mm 4.2mm 1.05mm;
    }
    .kta-inactive-stamp {
        position: absolute;
        inset: 0;
        z-index: 8;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.28);
        pointer-events: none;
    }
    .kta-inactive-stamp span {
        transform: rotate(-18deg);
        border: 0.35mm solid #b91c1c;
        color: #b91c1c;
        font-weight: 800;
        font-size: 4.4mm;
        letter-spacing: 0.12em;
        padding: 0.6mm 1.8mm;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.72);
    }
    @media print {
        .kta-stage-label { display: none !important; }
        .kta-print-wrap { gap: 8mm; }
        .kta-stage {
            height: var(--kta-h);
            page-break-inside: avoid;
        }
        .kta-id1 { transform: none; }
        .kta-demo {
            box-shadow: none;
            border-radius: 0;
        }
        .print-id .kta-demo,
        .print-a4 .kta-demo {
            width: 85.6mm;
            height: 53.98mm;
        }
    }
    .kta-print-wrap.kta-exporting {
        --kta-preview-scale: 1;
    }
    .kta-print-wrap.kta-exporting .kta-id1 {
        transform: none !important;
    }
    .kta-print-wrap.kta-exporting .kta-stage {
        height: var(--kta-h);
    }
    .kta-print-wrap.kta-exporting .kta-stage-label {
        visibility: hidden;
    }
    .kta-print-wrap.kta-exporting .kta-demo {
        box-shadow: none;
    }
</style>
