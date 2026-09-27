<style>
    .kta-demo {
        container-type: inline-size;
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        aspect-ratio: 1024 / 645;
        background: #ffffff;
        overflow: hidden;
        border-radius: 0.6rem;
        border: 1px solid #e7e1d8;
        box-shadow: 0 10px 28px rgba(7, 20, 34, 0.16);
        font-family: ui-sans-serif, system-ui, sans-serif;
        color: #111111;
    }
    .kta-demo p { margin: 0; }
    .kta-demo img { pointer-events: none; }
    .kta-ornamen { position: absolute; inset: 0; z-index: 0; pointer-events: none; }
    .kta-watermark {
        position: absolute;
        left: 50%;
        top: 46%;
        width: 34%;
        transform: translate(-50%, -50%);
        opacity: 0.07;
        object-fit: contain;
    }
    .kta-demo-back .kta-watermark {
        top: 40%;
        width: 38%;
        opacity: 0.13;
    }
    .kta-ornamen-kiri-atas {
        position: absolute;
        top: 0;
        left: 0;
        width: 12.2%;
        height: auto;
        object-fit: contain;
        object-position: top left;
    }
    .kta-ornamen-kanan-atas {
        position: absolute;
        top: 0;
        right: 0;
        width: 11.4%;
        height: auto;
        object-fit: contain;
        object-position: top right;
    }
    .kta-ornamen-kanan-bawah {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 10.4%;
        height: auto;
        object-fit: contain;
        object-position: bottom right;
        z-index: 1;
    }
    .kta-lambang { display: none; }
    .kta-demo-back .kta-lambang {
        display: block;
        position: absolute;
        top: 5.6%;
        right: 8.2%;
        width: 7.6%;
        object-fit: contain;
        z-index: 2;
    }
    .kta-isi {
        position: relative;
        z-index: 2;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 0;
        padding: 3.4% 5.2% 1.6%;
    }
    .kta-isi-belakang {
        padding-top: 4.8%;
        padding-right: 16%;
        padding-bottom: 3.2%;
    }
    .kta-kop {
        text-align: center;
        color: #0c2244;
        line-height: 1.2;
    }
    .kta-kop-logo {
        height: 11.6cqi;
        margin: 0 auto 0.7cqi;
        object-fit: contain;
        display: block;
    }
    .kta-kop-nama { font-size: 2.4cqi; font-weight: 800; letter-spacing: 0.02em; }
    .kta-kop-singkatan { font-size: 3.05cqi; font-weight: 800; letter-spacing: 0.38em; margin-left: 0.38em; }
    .kta-kop-alamat { font-size: 1.55cqi; margin-top: 0.35cqi; font-weight: 600; }
    .kta-judul-depan {
        margin-top: 2.4cqi;
        text-align: center;
        color: #111;
        font-size: 3.25cqi;
        font-weight: 800;
        letter-spacing: 0.04em;
    }
    .kta-baris-utama {
        display: grid;
        grid-template-columns: 16.6% minmax(0, 1fr) 16.8%;
        gap: 2.4%;
        align-items: start;
        margin-top: 2.2cqi;
        flex: 1;
        min-height: 0;
    }
    .kta-foto-wrap { position: relative; }
    .kta-foto {
        width: 100%;
        aspect-ratio: 3 / 3.8;
        background: #d7e7f2;
        border: 0.18cqi solid #c5d0d8;
        overflow: hidden;
    }
    .kta-foto img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .kta-foto-label {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2cqi;
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
        padding-top: 1.6cqi;
        min-width: 0;
    }
    .kta-nama {
        color: #8b1530;
        font-size: 4.2cqi;
        font-weight: 800;
        letter-spacing: 0.04em;
        line-height: 1.05;
        text-transform: uppercase;
        word-break: break-word;
    }
    .kta-nomor {
        margin-top: 1.1cqi;
        color: #8b1530;
        font-size: 3.15cqi;
        font-weight: 800;
        letter-spacing: 0.04em;
    }
    .kta-pd {
        margin-top: 1cqi;
        color: #0c2244;
        font-size: 1.8cqi;
        font-weight: 800;
        letter-spacing: 0.03em;
        line-height: 1.2;
        text-transform: uppercase;
    }
    .kta-qr {
        width: 100%;
        aspect-ratio: 1;
        background: #fff;
        padding: 0.4cqi;
        align-self: start;
    }
    .kta-qr canvas {
        width: 100%;
        height: 100%;
        display: block;
        image-rendering: pixelated;
    }
    .kta-berlaku {
        margin-top: auto;
        padding-top: 1.4cqi;
        padding-right: 7cqi;
        text-align: right;
        color: #0c2244;
        font-size: 1.85cqi;
        font-weight: 700;
        line-height: 1.15;
    }
    .kta-stempel-depan {
        position: absolute;
        right: -48%;
        bottom: 4%;
        width: 92%;
        object-fit: contain;
        z-index: 3;
    }
    .kta-judul-belakang {
        text-align: center;
        color: #8b1530;
        font-size: 3.5cqi;
        font-weight: 800;
        letter-spacing: 0.08em;
    }
    .kta-data {
        margin-top: 2cqi;
        display: grid;
        gap: 0.45cqi;
        color: #111;
        font-size: 2.15cqi;
        font-weight: 700;
        line-height: 1.45;
    }
    .kta-data-row {
        display: grid;
        grid-template-columns: 22cqi 2.2cqi minmax(0, 1fr);
        align-items: start;
        column-gap: 0.6cqi;
    }
    .kta-data-value {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .kta-ttd-blok {
        margin-top: auto;
        padding-top: 1.6cqi;
        text-align: center;
    }
    .kta-tanggal {
        font-size: 2.15cqi;
        font-weight: 700;
        color: #111;
    }
    .kta-pengurus {
        margin-top: 0.55cqi;
        font-size: 2.1cqi;
        font-weight: 800;
        color: #111;
    }
    .kta-ttd-area {
        position: relative;
        display: flex;
        align-items: stretch;
        justify-content: center;
        gap: 6%;
        margin-top: 1cqi;
        min-height: 14cqi;
    }
    .kta-stempel-belakang {
        position: absolute;
        left: 0;
        bottom: 8%;
        width: 17%;
        max-height: 86%;
        object-fit: contain;
        z-index: 3;
        pointer-events: none;
    }
    .kta-ttd-col {
        flex: 1;
        min-width: 0;
        max-width: 42%;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .kta-ttd-jabatan {
        font-size: 2.05cqi;
        font-weight: 700;
        color: #111;
        text-align: center;
        line-height: 1.15;
    }
    .kta-ttd-col img {
        width: auto;
        max-width: 78%;
        height: 5.8cqi;
        object-fit: contain;
        object-position: center;
        display: block;
        margin: 0.5cqi 0 auto;
    }
    .kta-ttd-nama {
        margin-top: 0.2cqi;
        font-size: 2.05cqi;
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
        font-size: 1.7cqi;
        font-weight: 700;
        line-height: 1.25;
        padding: 1.15cqi 4.5cqi 1.25cqi;
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
        border: 0.55cqi solid #b91c1c;
        color: #b91c1c;
        font-weight: 800;
        font-size: 6.2cqi;
        letter-spacing: 0.14em;
        padding: 0.8cqi 2.4cqi;
        text-transform: uppercase;
        background: rgba(255, 255, 255, 0.72);
    }
</style>
