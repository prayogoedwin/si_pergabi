@props(['title' => 'Masuk'])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — PERGABI</title>
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
                        saffron: {
                            500: '#ee6b24',
                            600: '#e25a12',
                            700: '#c94b10',
                        },
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: "Be Vietnam Pro", sans-serif;
            background:
                radial-gradient(ellipse 80% 50% at 50% 0%, rgba(238, 107, 36, 0.24), transparent 55%),
                radial-gradient(ellipse 60% 40% at 50% 100%, rgba(30, 79, 215, 0.22), transparent 55%),
                linear-gradient(165deg, #06101c 0%, #0c2244 48%, #1a1230 100%);
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        <a href="{{ route('home') }}" class="mb-5 text-center group">
            <img src="{{ asset('images/logo-pergabi.png') }}" alt="Lambang PERGABI" class="w-20 h-20 mx-auto object-contain drop-shadow-lg">
            <p class="mt-2 font-display text-3xl tracking-[0.18em] text-[#fff6ea] group-hover:text-[#f0c14b] transition-colors">PERGABI</p>
        </a>
        <main class="w-full max-w-md">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
