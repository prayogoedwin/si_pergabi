<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PERGABI — Sistem Informasi Keanggotaan</title>
    <meta name="description" content="Perkumpulan Guru Agama Buddha Indonesia. Sistem informasi keanggotaan nasional berjenjang PP, PD, dan PC.">
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pergabi.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700|cormorant-garamond:600,700" rel="stylesheet">
    <style>
        :root {
            --navy: #071422;
            --navy-mid: #102a4a;
            --saffron: #ee6b24;
            --saffron-deep: #c94b10;
            --gold: #f0c14b;
            --cream: #fff6ea;
            --blue: #1e4fd7;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
        }

        body {
            font-family: "Be Vietnam Pro", sans-serif;
            color: var(--cream);
            background:
                radial-gradient(ellipse 80% 55% at 50% 18%, rgba(238, 107, 36, 0.28), transparent 58%),
                radial-gradient(ellipse 70% 50% at 50% 100%, rgba(30, 79, 215, 0.28), transparent 55%),
                linear-gradient(165deg, #06101c 0%, #0c2244 48%, #1a1230 100%);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image: radial-gradient(rgba(255, 255, 255, 0.05) 0.6px, transparent 0.6px);
            background-size: 18px 18px;
            opacity: 0.35;
        }

        .shell {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1.25rem 1.25rem;
        }

        .stage {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            max-width: 40rem;
            margin: 0 auto;
            width: 100%;
        }

        .logo-wrap {
            position: relative;
            width: 11.5rem;
            height: 11.5rem;
            margin-bottom: 1.5rem;
        }

        .logo-wrap::before {
            content: "";
            position: absolute;
            inset: -18%;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(240, 193, 75, 0.45), rgba(238, 107, 36, 0.12) 55%, transparent 70%);
            filter: blur(8px);
        }

        .logo-wrap img {
            position: relative;
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: drop-shadow(0 12px 28px rgba(0, 0, 0, 0.35));
        }

        .kicker {
            margin: 0 0 0.45rem;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.28em;
            text-transform: uppercase;
            color: var(--gold);
        }

        h1 {
            margin: 0;
            font-family: "Cormorant Garamond", serif;
            font-size: clamp(3.2rem, 8vw, 5.2rem);
            font-weight: 700;
            letter-spacing: 0.12em;
            line-height: 0.95;
        }

        .fullname {
            margin: 0.85rem 0 0;
            font-size: clamp(0.92rem, 2.4vw, 1.05rem);
            font-weight: 500;
            color: rgba(255, 246, 234, 0.86);
            line-height: 1.5;
        }

        .tagline {
            margin: 0.45rem 0 0;
            font-size: 0.95rem;
            color: rgba(255, 246, 234, 0.62);
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            width: 100%;
            margin-top: 2.25rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: min(100%, 15.5rem);
            height: 4.15rem;
            padding: 0 2rem;
            border-radius: 999px;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease, border-color 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-daftar {
            color: #fff;
            background: linear-gradient(180deg, #f1843d 0%, #e25a12 100%);
            box-shadow: 0 14px 32px rgba(226, 90, 18, 0.38);
        }

        .btn-daftar:hover {
            box-shadow: 0 18px 36px rgba(226, 90, 18, 0.5);
        }

        .btn-masuk {
            color: var(--cream);
            border: 2px solid rgba(240, 193, 75, 0.9);
            background: rgba(255, 255, 255, 0.04);
        }

        .btn-masuk:hover {
            border-color: var(--gold);
            background: rgba(240, 193, 75, 0.12);
        }

        .levels {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.55rem;
            margin-top: 1.85rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.38rem 0.85rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: rgba(255, 246, 234, 0.82);
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .chip span {
            color: var(--gold);
        }

        footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.78rem;
            color: rgba(255, 246, 234, 0.42);
        }

        @media (max-width: 640px) {
            .logo-wrap {
                width: 9.25rem;
                height: 9.25rem;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="shell">
        <main class="stage">
            <div class="logo-wrap">
                <img src="{{ asset('images/logo-pergabi.png') }}" alt="Lambang PERGABI">
            </div>

            <p class="kicker">Sistem Informasi Keanggotaan</p>
            <h1>PERGABI</h1>
            <p class="fullname">Perkumpulan Guru Agama Buddha Indonesia</p>
            <p class="tagline">Keanggotaan nasional berjenjang dari pusat hingga cabang</p>

            <div class="actions">
                @auth
                    <a class="btn btn-daftar" href="{{ url('/dashboard') }}">Buka Dashboard</a>
                @else
                    <a class="btn btn-daftar" href="{{ route('daftar') }}">Daftar</a>
                    <a class="btn btn-masuk" href="{{ route('login') }}">Masuk</a>
                @endauth
            </div>

            <div class="levels">
                <div class="chip"><span>PP</span> Pengurus Pusat</div>
                <div class="chip"><span>PD</span> Pengurus Daerah</div>
                <div class="chip"><span>PC</span> Pengurus Cabang</div>
            </div>
        </main>

        <footer>&copy; {{ date('Y') }} PERGABI · Pengurus Pusat</footer>
    </div>
</body>
</html>
