@php
    $tanggalTerbit = ($anggota->tanggal_bergabung ?? now())->locale('id')->translatedFormat('d F Y');
@endphp

@include('kta._kartu-style')

<article id="kta-front" class="kta-demo kta-demo-front">
    <div class="kta-kop">
        <img src="{{ $identitas['logo_url'] }}" alt="Logo" class="kta-kop-logo">
        <p class="kta-kop-nama">{{ $identitas['nama_lengkap'] }}</p>
        <p class="kta-kop-singkatan">{{ $identitas['singkatan'] }}</p>
        <p class="kta-kop-alamat">{{ $identitas['alamat'] }}</p>
    </div>
    <p class="kta-nama">{{ $anggota->namaLengkap() }}</p>
    <p class="kta-nomor">{{ $anggota->nomor_anggota }}</p>
    <p class="kta-pd">{{ $anggota->labelPdPergabi() }}</p>
    <p class="kta-berlaku">Berlaku s.d. {{ $anggota->masaBerlakuLabel() }}</p>
    <div class="kta-foto">
        @if (! empty($fotoUrl))
            <img src="{{ $fotoUrl }}" alt="Foto {{ $anggota->namaLengkap() }}">
        @endif
    </div>
    @if (! empty($identitas['stempel_url']))
        <img src="{{ $identitas['stempel_url'] }}" alt="Stempel" class="kta-stempel-depan">
    @endif
    <div class="kta-qr"><canvas data-kta-qr width="160" height="160"></canvas></div>
</article>

<article id="kta-back" class="kta-demo kta-demo-back">
    <div class="kta-nilai">
        <div>{{ $anggota->namaLengkap() }}</div>
        <div>{{ $anggota->nomor_anggota }}</div>
        <div>{{ $anggota->nik }}</div>
        <div>{{ $anggota->ttlLabel() }}</div>
        <div>{{ $anggota->instansiLabel() }}</div>
        <div>{{ $anggota->alamatLabel() }}</div>
    </div>
    <p class="kta-tanggal">Jakarta, {{ $tanggalTerbit }}</p>
    <div class="kta-ttd-area">
        @if (! empty($identitas['stempel_url']))
            <img src="{{ $identitas['stempel_url'] }}" alt="Stempel" class="kta-stempel-belakang">
        @else
            <span class="kta-stempel-belakang"></span>
        @endif
        <div class="kta-ttd-col">
            @if (! empty($identitas['ttd_ketua_umum_url']))
                <img src="{{ $identitas['ttd_ketua_umum_url'] }}" alt="TTD Ketua Umum">
            @endif
            <p>{{ $identitas['nama_ketua_umum'] }}</p>
        </div>
        <div class="kta-ttd-col">
            @if (! empty($identitas['ttd_sekretaris_jenderal_url']))
                <img src="{{ $identitas['ttd_sekretaris_jenderal_url'] }}" alt="TTD Sekretaris Jenderal">
            @endif
            <p>{{ $identitas['nama_sekretaris_jenderal'] }}</p>
        </div>
    </div>
    <p class="kta-visi">Visi: {{ $identitas['visi'] }}</p>
</article>
