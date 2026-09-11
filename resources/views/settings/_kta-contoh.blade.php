@php
    $tanggalTerbit = now()->locale('id')->translatedFormat('d F Y');
@endphp

@include('kta._kartu-style', [
    'halamanDepanUrl' => $values['kta_halaman_depan_url'],
    'halamanBelakangUrl' => $values['kta_halaman_belakang_url'],
])

<div class="bg-white dark:bg-navy-900 rounded-xl border border-[#e4ddd3] dark:border-gold-400/25 p-6">
    <h2 class="text-lg font-semibold text-navy-900 dark:text-cream-100">Contoh tampilan</h2>
    <p class="text-sm text-navy-800/50 dark:text-cream-100/60 mt-1 mb-4">Nama anggota memakai teks demo. Logo, stempel, TTD, dan teks organisasi mengikuti isian di bawah — termasuk berkas yang baru dipilih.</p>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        <div>
            <p class="text-xs font-medium text-navy-800/60 dark:text-cream-100/60 mb-2">Halaman depan</p>
            <article class="kta-demo kta-demo-front">
                <div class="kta-kop">
                    <img :src="logo_url" alt="Logo" class="kta-kop-logo">
                    <p class="kta-kop-nama" x-text="nama_lengkap"></p>
                    <p class="kta-kop-singkatan" x-text="singkatan"></p>
                    <p class="kta-kop-alamat" x-text="alamat"></p>
                </div>
                <p class="kta-nama">demo</p>
                <p class="kta-nomor">demo</p>
                <div class="kta-foto">
                    <span class="kta-foto-label">DEMO</span>
                </div>
                <img :src="stempel_url" alt="Stempel" class="kta-stempel-depan">
                <div class="kta-qr"><canvas x-ref="qr" width="160" height="160"></canvas></div>
            </article>
        </div>
        <div>
            <p class="text-xs font-medium text-navy-800/60 dark:text-cream-100/60 mb-2">Halaman belakang</p>
            <article class="kta-demo kta-demo-back">
                <div class="kta-nilai">
                    <div>demo</div>
                    <div>demo</div>
                    <div>demo</div>
                    <div>demo</div>
                    <div>demo</div>
                    <div>demo</div>
                </div>
                <p class="kta-tanggal">Jakarta, {{ $tanggalTerbit }}</p>
                <div class="kta-ttd-area">
                    <img :src="stempel_url" alt="Stempel" class="kta-stempel-belakang">
                    <div class="kta-ttd-col">
                        <img :src="ttd_ketua_umum_url" alt="TTD Ketua Umum">
                        <p x-text="nama_ketua_umum"></p>
                    </div>
                    <div class="kta-ttd-col">
                        <img :src="ttd_sekretaris_jenderal_url" alt="TTD Sekretaris Jenderal">
                        <p x-text="nama_sekretaris_jenderal"></p>
                    </div>
                </div>
                <p class="kta-visi" x-text="'Visi: ' + visi"></p>
            </article>
        </div>
    </div>
</div>
