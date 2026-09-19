<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi KTA — PERGABI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pergabi.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700|cormorant-garamond:600,700" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Be Vietnam Pro', 'sans-serif'],
                        display: ['Cormorant Garamond', 'serif'],
                    },
                    colors: {
                        saffron: { 500: '#ee6b24', 600: '#e25a12' },
                        navy: { 800: '#0c2244', 900: '#071422' },
                        gold: { 400: '#f0c14b' },
                        cream: { 50: '#fff8f1' },
                    },
                },
            },
        }
    </script>
</head>
<body class="min-h-dvh bg-cream-50 text-navy-900 antialiased" style="font-family: 'Be Vietnam Pro', sans-serif;">
    <main class="max-w-md mx-auto px-4 py-8">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo-pergabi.png') }}" alt="PERGABI" class="h-16 w-16 mx-auto object-contain">
            <p class="font-display text-3xl tracking-[0.16em] text-navy-900 mt-2">PERGABI</p>
            <p class="text-sm text-navy-800/70">Verifikasi kartu tanda anggota</p>
        </div>

        @if ($valid && $anggota)
            <div class="mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-center">
                <p class="text-sm font-semibold text-emerald-800">Kartu ini valid</p>
                <p class="text-xs text-emerald-800/80 mt-1">Nomor anggota terdaftar dan status keanggotaan aktif.</p>
            </div>
        @elseif ($anggota)
            <div class="mb-4 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-center">
                <p class="text-sm font-semibold text-red-800">Kartu tidak valid</p>
                <p class="text-xs text-red-800/80 mt-1">Data ditemukan, tetapi status keanggotaan bukan aktif.</p>
            </div>
        @else
            <div class="mb-4 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-center">
                <p class="text-sm font-semibold text-red-800">Tidak valid atau tidak ditemukan</p>
                <p class="text-xs text-red-800/80 mt-1">
                    @if ($kode !== '')
                        Nomor anggota <span class="font-semibold">{{ $kode }}</span> tidak terdaftar.
                    @else
                        Kode verifikasi tidak disertakan pada tautan QR.
                    @endif
                </p>
            </div>
        @endif

        @if ($anggota)
            <article class="rounded-2xl bg-white border border-gold-400/30 p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="h-24 w-20 rounded-xl overflow-hidden bg-navy-900/5 border border-gold-400/30 shrink-0">
                        @if ($anggota->foto_path)
                            <img src="{{ $anggota->urlFotoVerifikasiQr() }}" alt="Foto {{ $anggota->namaLengkap() }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="font-display text-2xl leading-tight">{{ $anggota->namaLengkap() }}</p>
                        <p class="text-sm font-semibold mt-1">{{ $anggota->nomor_anggota }}</p>
                        <p class="text-xs text-navy-800/70 mt-1">{{ $anggota->labelPdPergabi() }}</p>
                    </div>
                </div>

                <dl class="mt-5 space-y-2 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">Nama lengkap</dt>
                        <dd class="text-right font-medium">{{ $anggota->namaLengkap() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">NTA</dt>
                        <dd class="text-right font-semibold">{{ $anggota->nomor_anggota }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">NIK</dt>
                        <dd class="text-right">{{ $anggota->nik }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">TTL</dt>
                        <dd class="text-right">{{ $anggota->ttlLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">Instansi</dt>
                        <dd class="text-right">{{ $anggota->instansiLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">Alamat</dt>
                        <dd class="text-right">{{ $anggota->alamatLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">Status</dt>
                        <dd class="font-semibold {{ $anggota->isAktif() ? 'text-emerald-700' : 'text-red-700' }}">{{ $anggota->statusLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">PD</dt>
                        <dd class="text-right">{{ $anggota->labelPdPergabi() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">Masa berlaku</dt>
                        <dd>{{ $anggota->masaBerlakuLabel() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-navy-800/50">Tanggal verifikasi</dt>
                        <dd>{{ $anggota->tanggalVerifikasiLabel() }}</dd>
                    </div>
                </dl>
            </article>
        @endif

        <p class="mt-6 text-center text-sm font-semibold text-red-700 leading-relaxed">
            NB: Pastikan data pada halaman ini sama dengan yang tercetak pada Kartu Tanda Anggota PERGABI (nama, NTA, NIK, TTL, instansi, dan alamat). KTA hanya sah jika status keanggotaan Aktif. Jika data berbeda atau status tidak aktif, kartu tersebut tidak berlaku.
        </p>
    </main>
</body>
</html>
