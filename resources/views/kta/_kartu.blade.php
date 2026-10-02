@php
    $tanggalTerbit = ($anggota->tanggal_bergabung ?? now())->locale('id')->translatedFormat('d F Y');
    $namaDepanKartu = strtoupper((string) ($anggota->nama ?: $anggota->namaLengkap()));
@endphp

@include('kta._kartu-style')

<div class="kta-stage">
    <p class="kta-stage-label">Halaman depan · ukuran KTP / SIM (85,60 × 53,98 mm)</p>
    <div class="kta-id1">
        <article id="kta-front" class="kta-demo kta-demo-front">
            @include('kta._ornamen')
            <div class="kta-isi">
                <div class="kta-kop">
                    <img src="{{ $identitas['logo_url'] }}" alt="Logo" class="kta-kop-logo">
                    <p class="kta-kop-nama">{{ $identitas['nama_lengkap'] }}</p>
                    <p class="kta-kop-singkatan">{{ $identitas['singkatan'] }}</p>
                    <p class="kta-kop-alamat">{{ $identitas['alamat'] }}</p>
                </div>
                <p class="kta-judul-depan">KARTU TANDA ANGGOTA</p>
                <div class="kta-baris-utama">
                    <div class="kta-foto-wrap">
                        <div class="kta-foto">
                            @if (! empty($fotoUrl))
                                <img src="{{ $fotoUrl }}" alt="Foto {{ $anggota->namaLengkap() }}">
                            @endif
                        </div>
                        @if (! empty($identitas['stempel_url']))
                            <img src="{{ $identitas['stempel_url'] }}" alt="Stempel" class="kta-stempel-depan">
                        @endif
                    </div>
                    <div class="kta-identitas">
                        <p class="kta-nama">{{ $namaDepanKartu }}</p>
                        <p class="kta-nomor">{{ $anggota->nomor_anggota }}</p>
                        <p class="kta-pd">{{ $anggota->labelPdPergabi() }}</p>
                    </div>
                    <div class="kta-qr"><canvas data-kta-verifikasi width="160" height="160"></canvas></div>
                </div>
                <p class="kta-berlaku">KTA ini berlaku s.d. {{ $anggota->masaBerlakuLabel() }}</p>
            </div>
            <p class="kta-visi">Visi: {{ $identitas['visi'] }}</p>
            @if (! $anggota->isAktif())
                <div class="kta-inactive-stamp"><span>Tidak aktif</span></div>
            @endif
        </article>
    </div>
</div>

<div class="kta-stage">
    <p class="kta-stage-label">Halaman belakang · ukuran KTP / SIM (85,60 × 53,98 mm)</p>
    <div class="kta-id1">
        <article id="kta-back" class="kta-demo kta-demo-back">
            @include('kta._ornamen')
            <div class="kta-isi kta-isi-belakang">
                <p class="kta-judul-belakang">DATA ANGGOTA</p>
                <div class="kta-data">
                    <div class="kta-data-row">
                        <span>Nama Lengkap</span><span>:</span>
                        <span class="kta-data-value">{{ $anggota->namaLengkap() }}</span>
                    </div>
                    <div class="kta-data-row">
                        <span>NTA</span><span>:</span>
                        <span class="kta-data-value">{{ $anggota->nomor_anggota }}</span>
                    </div>
                    <div class="kta-data-row">
                        <span>NIK</span><span>:</span>
                        <span class="kta-data-value">{{ $anggota->nik }}</span>
                    </div>
                    <div class="kta-data-row">
                        <span>Instansi</span><span>:</span>
                        <span class="kta-data-value">{{ $anggota->instansiLabel() }}</span>
                    </div>
                    <div class="kta-data-row">
                        <span>Alamat</span><span>:</span>
                        <span class="kta-data-value">{{ $anggota->alamatLabel() }}</span>
                    </div>
                </div>
                <div class="kta-ttd-blok">
                    <p class="kta-tanggal">Jakarta, {{ $tanggalTerbit }}</p>
                    <p class="kta-pengurus">Pengurus Pusat</p>
                    <div class="kta-ttd-area">
                        <div class="kta-ttd-col">
                            <p class="kta-ttd-jabatan">Ketua Umum,</p>
                            <canvas class="kta-ttd-qr" data-kta-pengesahan="ketua" width="160" height="160"></canvas>
                            <p class="kta-ttd-nama">{{ $identitas['nama_ketua_umum'] }}</p>
                        </div>
                        <div class="kta-ttd-col">
                            <p class="kta-ttd-jabatan">Sekretaris Jenderal,</p>
                            <canvas class="kta-ttd-qr" data-kta-pengesahan="sekjen" width="160" height="160"></canvas>
                            <p class="kta-ttd-nama">{{ $identitas['nama_sekretaris_jenderal'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>
