@php
    $halamanDepanUrl = $halamanDepanUrl ?? $identitas['kta_halaman_depan_url'] ?? $values['kta_halaman_depan_url'] ?? '';
    $halamanBelakangUrl = $halamanBelakangUrl ?? $identitas['kta_halaman_belakang_url'] ?? $values['kta_halaman_belakang_url'] ?? '';
@endphp

<style>
    .kta-demo {
        container-type: inline-size;
        position: relative;
        width: 100%;
        aspect-ratio: 1024 / 645;
        background-size: 100% 100%;
        background-repeat: no-repeat;
        background-position: center;
        overflow: hidden;
        border-radius: 0.6rem;
        box-shadow: 0 10px 28px rgba(7, 20, 34, 0.16);
    }
    .kta-demo-front { background-image: url('{{ $halamanDepanUrl }}'); }
    .kta-demo-back { background-image: url('{{ $halamanBelakangUrl }}'); }
    .kta-demo img { pointer-events: none; }
    .kta-kop {
        position: absolute;
        top: 4%;
        left: 12%;
        right: 12%;
        text-align: center;
        color: #0c2244;
        line-height: 1.2;
    }
    .kta-kop-logo { height: 11.5cqi; margin: 0 auto 0.8cqi; object-fit: contain; }
    .kta-kop-nama { font-size: 2.35cqi; font-weight: 800; letter-spacing: 0.02em; }
    .kta-kop-singkatan { font-size: 3.05cqi; font-weight: 800; letter-spacing: 0.42em; margin-left: 0.42em; }
    .kta-kop-alamat { font-size: 1.55cqi; margin-top: 0.35cqi; font-weight: 600; }
    .kta-nama {
        position: absolute;
        top: 50.5%;
        left: 10%;
        right: 10%;
        text-align: center;
        color: #8b1530;
        font-size: 4.4cqi;
        font-weight: 800;
        letter-spacing: 0.04em;
        line-height: 1;
        text-transform: uppercase;
    }
    .kta-nomor {
        position: absolute;
        top: 57.2%;
        left: 10%;
        right: 10%;
        text-align: center;
        color: #8b1530;
        font-size: 3.3cqi;
        font-weight: 800;
        letter-spacing: 0.04em;
    }
    .kta-foto {
        position: absolute;
        left: 7.6%;
        top: 58.8%;
        width: 16.8%;
        height: 29.5%;
        object-fit: cover;
        background: #d7e7f2;
        border: 0.15cqi solid #c5d0d8;
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
    .kta-stempel-depan {
        position: absolute;
        left: 15.5%;
        top: 68%;
        width: 15.5%;
        object-fit: contain;
        z-index: 2;
    }
    .kta-qr {
        position: absolute;
        right: 7.2%;
        top: 58.5%;
        width: 17.6%;
        aspect-ratio: 1;
        background: #fff;
        padding: 0.6cqi;
    }
    .kta-qr canvas { width: 100%; height: 100%; display: block; }
    .kta-nilai {
        position: absolute;
        top: 16.8%;
        left: 33.6%;
        width: 48%;
        color: #111;
        font-size: 2.15cqi;
        font-weight: 700;
        line-height: 1.62;
    }
    .kta-nilai > div {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .kta-tanggal {
        position: absolute;
        top: 53.6%;
        left: 10%;
        right: 10%;
        text-align: center;
        font-size: 2.15cqi;
        font-weight: 700;
        color: #111;
    }
    .kta-ttd-area {
        position: absolute;
        left: 22%;
        right: 16%;
        top: 69%;
        height: 20%;
        display: flex;
        align-items: flex-end;
        gap: 2%;
        z-index: 2;
    }
    .kta-stempel-belakang {
        position: absolute;
        left: 5%;
        bottom: 5.2cqi;
        width: 17.5%;
        max-height: 88%;
        object-fit: contain;
        z-index: 3;
        pointer-events: none;
    }
    .kta-ttd-col {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        padding-bottom: 0.2cqi;
    }
    .kta-ttd-col img {
        width: auto;
        max-width: 78%;
        height: 7.6cqi;
        object-fit: contain;
        object-position: bottom center;
        display: block;
    }
    .kta-ttd-col p {
        margin-top: 0.15cqi;
        font-size: 2.05cqi;
        font-weight: 700;
        color: #111;
        text-align: center;
        line-height: 1.1;
        white-space: nowrap;
    }
    .kta-visi {
        position: absolute;
        left: 4.2%;
        right: 4.2%;
        bottom: 2.4%;
        background: #6b1220;
        color: #fff;
        text-align: center;
        font-size: 1.7cqi;
        font-weight: 700;
        line-height: 1.25;
        padding: 0.85cqi 1.6cqi;
        border-radius: 1.4cqi;
    }
</style>
