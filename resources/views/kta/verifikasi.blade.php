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

        <article class="rounded-2xl bg-white border border-gold-400/30 p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="h-24 w-20 rounded-xl overflow-hidden bg-navy-900/5 border border-gold-400/30">
                    @if ($anggota->foto_path)
                        <img src="{{ route('kta.foto', $anggota->nomor_anggota) }}" alt="Foto {{ $anggota->nama }}" class="h-full w-full object-cover">
                    @endif
                </div>
                <div>
                    <p class="font-display text-2xl leading-tight">{{ $anggota->namaLengkap() }}</p>
                    <p class="text-sm font-semibold mt-1">{{ $anggota->nomor_anggota }}</p>
                </div>
            </div>

            <dl class="mt-5 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-navy-800/50">Status</dt>
                    <dd class="font-semibold {{ $anggota->isAktif() ? 'text-saffron-600' : 'text-red-700' }}">{{ $anggota->statusLabel() }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-navy-800/50">PD</dt>
                    <dd>{{ $anggota->pd?->nama ?? $anggota->pd_kode }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-navy-800/50">Masa berlaku</dt>
                    <dd>{{ $anggota->masa_berlaku_hingga?->format('d M Y') ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-navy-800/50">Tanggal verifikasi</dt>
                    <dd>{{ $anggota->statusLogs->firstWhere('status_ke', \App\Models\Anggota::STATUS_AKTIF)?->created_at?->format('d M Y') ?: '—' }}</dd>
                </div>
            </dl>
        </article>
    </main>
</body>
</html>
