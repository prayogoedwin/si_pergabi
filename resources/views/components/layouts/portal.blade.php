@props(['title' => 'Portal Anggota', 'wide' => false])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title }} — PERGABI</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pergabi.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700|cormorant-garamond:600,700" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Be Vietnam Pro', 'sans-serif'],
                        display: ['Cormorant Garamond', 'serif'],
                    },
                    colors: {
                        saffron: { 50: '#fff4ec', 500: '#ee6b24', 600: '#e25a12', 700: '#c94b10' },
                        navy: { 800: '#0c2244', 900: '#071422', 950: '#06101c' },
                        gold: { 400: '#f0c14b', 500: '#e8b42e' },
                        cream: { 50: '#fff8f1', 100: '#fff6ea' },
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: "Be Vietnam Pro", sans-serif; }
        @media print {
            .portal-chrome { display: none !important; }
            body { background: white !important; }
            main { padding: 0 !important; }
        }
    </style>
    {{ $head ?? '' }}
</head>
<body class="min-h-dvh bg-cream-50 text-navy-900 antialiased">
    <header class="portal-chrome sticky top-0 z-30 bg-navy-900 text-cream-100 border-b border-gold-400/30">
        <div class="{{ $wide ? 'max-w-5xl' : 'max-w-3xl' }} mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ route('portal.show') }}" class="flex items-center gap-2 min-w-0">
                <img src="{{ asset('images/logo-pergabi.png') }}" alt="Logo PERGABI" class="h-8 w-8 object-contain shrink-0">
                <span class="font-display text-xl tracking-[0.14em] text-gold-400">PERGABI</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs sm:text-sm text-cream-100/80 hover:text-gold-400">Keluar</button>
            </form>
        </div>
        <nav class="hidden md:block border-t border-gold-400/15">
            <div class="{{ $wide ? 'max-w-5xl' : 'max-w-3xl' }} mx-auto px-4 flex gap-1">
                <a href="{{ route('portal.show') }}"
                    class="px-4 py-2.5 text-sm {{ request()->routeIs('portal.show') ? 'text-gold-400 border-b-2 border-gold-400' : 'text-cream-100/75 hover:text-gold-400' }}">Beranda</a>
                <a href="{{ route('portal.kta') }}"
                    class="px-4 py-2.5 text-sm {{ request()->routeIs('portal.kta') ? 'text-gold-400 border-b-2 border-gold-400' : 'text-cream-100/75 hover:text-gold-400' }}">Kartu digital</a>
                <a href="{{ route('portal.qr') }}"
                    class="px-4 py-2.5 text-sm {{ request()->routeIs('portal.qr') ? 'text-gold-400 border-b-2 border-gold-400' : 'text-cream-100/75 hover:text-gold-400' }}">QR Code</a>
                <a href="{{ route('portal.profil') }}"
                    class="px-4 py-2.5 text-sm {{ request()->routeIs('portal.profil') ? 'text-gold-400 border-b-2 border-gold-400' : 'text-cream-100/75 hover:text-gold-400' }}">Profil</a>
            </div>
        </nav>
    </header>

    <main class="{{ $wide ? 'max-w-5xl' : 'max-w-3xl' }} mx-auto px-4 pt-5 pb-[calc(5.75rem+env(safe-area-inset-bottom))] md:pb-10">
        @session('status')
            <div class="mb-4 rounded-xl border-l-4 border-saffron-600 bg-saffron-50 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endsession

        {{ $slot }}
    </main>

    <nav class="portal-chrome md:hidden fixed bottom-0 inset-x-0 z-30 bg-navy-900 border-t border-gold-400/25 pb-[env(safe-area-inset-bottom)]">
        <div class="grid grid-cols-4 text-center text-[11px]">
            <a href="{{ route('portal.show') }}" class="py-2.5 {{ request()->routeIs('portal.show') ? 'text-gold-400' : 'text-cream-100/70' }}">
                <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10.5 12 3l9 7.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"/></svg>
                Beranda
            </a>
            <a href="{{ route('portal.kta') }}" class="py-2.5 {{ request()->routeIs('portal.kta') ? 'text-gold-400' : 'text-cream-100/70' }}">
                <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M8 14h8"/></svg>
                Kartu
            </a>
            <a href="{{ route('portal.qr') }}" class="py-2.5 {{ request()->routeIs('portal.qr') ? 'text-gold-400' : 'text-cream-100/70' }}">
                <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7V4h3M17 4h3v3M20 17v3h-3M7 20H4v-3M8 8h3v3H8V8zm5 5h3v3h-3v-3z"/></svg>
                QR
            </a>
            <a href="{{ route('portal.profil') }}" class="py-2.5 {{ request()->routeIs('portal.profil') ? 'text-gold-400' : 'text-cream-100/70' }}">
                <svg class="mx-auto h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 20a8 4 0 0116 0"/></svg>
                Profil
            </a>
        </div>
    </nav>
</body>
</html>
