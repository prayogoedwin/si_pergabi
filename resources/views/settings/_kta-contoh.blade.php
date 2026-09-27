@php
    $tanggalTerbit = now()->locale('id')->translatedFormat('d F Y');
@endphp

@include('kta._kartu-style')

<div class="bg-white dark:bg-navy-900 rounded-xl border border-[#e4ddd3] dark:border-gold-400/25 p-6">
    <h2 class="text-lg font-semibold text-navy-900 dark:text-cream-100">Contoh tampilan</h2>
    <p class="text-sm text-navy-800/50 dark:text-cream-100/60 mt-1 mb-4">Nama anggota memakai teks demo. Logo, stempel, TTD, dan teks organisasi mengikuti isian di bawah — termasuk berkas yang baru dipilih.</p>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <div>
            <p class="text-xs font-medium text-navy-800/60 dark:text-cream-100/60 mb-2">Halaman depan</p>
            <article class="kta-demo kta-demo-front">
                @include('kta._ornamen')
                <div class="kta-isi">
                    <div class="kta-kop">
                        <img :src="logo_url" alt="Logo" class="kta-kop-logo">
                        <p class="kta-kop-nama" x-text="nama_lengkap"></p>
                        <p class="kta-kop-singkatan" x-text="singkatan"></p>
                        <p class="kta-kop-alamat" x-text="alamat"></p>
                    </div>
                    <p class="kta-judul-depan">KARTU TANDA ANGGOTA</p>
                    <div class="kta-baris-utama">
                        <div class="kta-foto-wrap">
                            <div class="kta-foto">
                                <span class="kta-foto-label">DEMO</span>
                            </div>
                            <img :src="stempel_url" alt="Stempel" class="kta-stempel-depan">
                        </div>
                        <div class="kta-identitas">
                            <p class="kta-nama">demo</p>
                            <p class="kta-nomor">demo</p>
                            <p class="kta-pd">PD PERGABI demo</p>
                        </div>
                        <div class="kta-qr"><canvas x-ref="qr" width="160" height="160"></canvas></div>
                    </div>
                    <p class="kta-berlaku">KTA ini berlaku s.d. demo</p>
                </div>
                <p class="kta-visi" x-text="'Visi: ' + visi"></p>
            </article>
        </div>
        <div>
            <p class="text-xs font-medium text-navy-800/60 dark:text-cream-100/60 mb-2">Halaman belakang</p>
            <article class="kta-demo kta-demo-back">
                @include('kta._ornamen')
                <div class="kta-isi kta-isi-belakang">
                    <p class="kta-judul-belakang">DATA ANGGOTA</p>
                    <div class="kta-data">
                        <div class="kta-data-row"><span>Nama Lengkap</span><span>:</span><span class="kta-data-value">demo</span></div>
                        <div class="kta-data-row"><span>NTA</span><span>:</span><span class="kta-data-value">demo</span></div>
                        <div class="kta-data-row"><span>NIK</span><span>:</span><span class="kta-data-value">demo</span></div>
                        <div class="kta-data-row"><span>Instansi</span><span>:</span><span class="kta-data-value">demo</span></div>
                        <div class="kta-data-row"><span>Alamat</span><span>:</span><span class="kta-data-value">demo</span></div>
                    </div>
                    <div class="kta-ttd-blok">
                        <p class="kta-tanggal">Jakarta, {{ $tanggalTerbit }}</p>
                        <p class="kta-pengurus">Pengurus Pusat</p>
                        <div class="kta-ttd-area">
                            <img :src="stempel_url" alt="Stempel" class="kta-stempel-belakang">
                            <div class="kta-ttd-col">
                                <p class="kta-ttd-jabatan">Ketua Umum,</p>
                                <img :src="ttd_ketua_umum_url" alt="TTD Ketua Umum">
                                <p class="kta-ttd-nama" x-text="nama_ketua_umum"></p>
                            </div>
                            <div class="kta-ttd-col">
                                <p class="kta-ttd-jabatan">Sekretaris Jenderal,</p>
                                <img :src="ttd_sekretaris_jenderal_url" alt="TTD Sekretaris Jenderal">
                                <p class="kta-ttd-nama" x-text="nama_sekretaris_jenderal"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </div>
</div>
