<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'PERGABI') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-pergabi.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=be-vietnam-pro:400,500,600,700|cormorant-garamond:600,700" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Be Vietnam Pro', 'sans-serif'],
                        display: ['Cormorant Garamond', 'serif'],
                    },
                    colors: {
                        saffron: {
                            50: '#fff4ec',
                            500: '#ee6b24',
                            600: '#e25a12',
                            700: '#c94b10',
                        },
                        navy: {
                            800: '#0c2244',
                            900: '#071422',
                            950: '#06101c',
                        },
                        gold: {
                            400: '#f0c14b',
                            500: '#e8b42e',
                        },
                        cream: {
                            50: '#f4f0ea',
                            100: '#efe8dc',
                        },
                        sidebar: {
                            DEFAULT: '#f3ece3',
                            foreground: '#1a2b40',
                            accent: '#0c2244',
                            'accent-foreground': '#ffffff',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: "Be Vietnam Pro", sans-serif; }
        .sidebar-transition { transition: width 0.3s ease; }
        .content-transition { transition: margin-left 0.3s ease; }
        .custom-scrollbar::-webkit-scrollbar { width: 8px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(12, 34, 68, 0.18); border-radius: 4px; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(240, 193, 75, 0.25); }
        .dark .bg-sidebar { background-color: #06101c; }
        .dark .text-sidebar-foreground { color: #fff6ea; }
        .dark .bg-sidebar-accent { background-color: #ee6b24; }
        .dark .hover\:bg-sidebar-accent:hover { background-color: #ee6b24; }

        @media print {
            header, aside, footer { display: none !important; }
            .print\:hidden { display: none !important; }
            body { background: white !important; }
            main, .flex-1 { overflow: visible !important; }
            .p-6 { padding: 0 !important; }
        }

        input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="range"]),
        select,
        textarea {
            border: 1px solid #d6cfc3 !important;
            background-color: #ffffff !important;
            color: #0c2244;
            padding: 0.6rem 0.85rem;
        }
        input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]):focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #0c2244 !important;
            box-shadow: 0 0 0 3px rgba(12, 34, 68, 0.12);
        }
        .dark input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]):not([type="submit"]):not([type="button"]):not([type="image"]):not([type="range"]),
        .dark select,
        .dark textarea {
            border-color: rgba(240, 193, 75, 0.4) !important;
            background-color: #06101c !important;
            color: #efe8dc;
        }
        .dark input:focus,
        .dark select:focus,
        .dark textarea:focus {
            border-color: #f0c14b !important;
            box-shadow: 0 0 0 3px rgba(240, 193, 75, 0.18);
        }
    </style>

    
    <script>
        window.setAppearance = function(appearance) {
            let setDark = () => document.documentElement.classList.add('dark')
            let setLight = () => document.documentElement.classList.remove('dark')
            let setButtons = (appearance) => {
                document.querySelectorAll('button[onclick^="setAppearance"]').forEach((button) => {
                    button.setAttribute('aria-pressed', String(appearance === button.value))
                })
            }
            if (appearance === 'system') {
                let media = window.matchMedia('(prefers-color-scheme: dark)')
                window.localStorage.removeItem('appearance')
                media.matches ? setDark() : setLight()
            } else if (appearance === 'dark') {
                window.localStorage.setItem('appearance', 'dark')
                setDark()
            } else if (appearance === 'light') {
                window.localStorage.setItem('appearance', 'light')
                setLight()
            }
            if (document.readyState === 'complete') {
                setButtons(appearance)
            } else {
                document.addEventListener("DOMContentLoaded", () => setButtons(appearance))
            }
        }
        window.setAppearance(
            "{{ auth()->user()->theme_preference ?? '' }}" || 
            window.localStorage.getItem('appearance') || 
            'system'
        )
    </script>
</head>

<body class="bg-cream-50 dark:bg-navy-950 text-navy-900 dark:text-cream-100 antialiased" x-data="{
    sidebarOpen: localStorage.getItem('sidebarOpen') === null ? window.innerWidth >= 1024 : (localStorage.getItem('sidebarOpen') === 'true' && window.innerWidth >= 1024),
    toggleSidebar() {
        this.sidebarOpen = !this.sidebarOpen;
        localStorage.setItem('sidebarOpen', this.sidebarOpen);
    },
    temporarilyOpenSidebar() {
        if (!this.sidebarOpen) {
            this.sidebarOpen = true;
            localStorage.setItem('sidebarOpen', true);
        }
    },
    formSubmitted: false,
}">

    <!-- Main Container -->
    <div class="min-h-screen flex flex-col">

        <x-layouts.app.header />

        <!-- Main Content Area -->
        <div class="flex flex-1 overflow-hidden">

            <x-layouts.app.sidebar />

            <!-- Main Content -->
            <main class="flex-1 flex flex-col overflow-auto bg-cream-50 dark:bg-navy-950 content-transition">
                <div class="flex-1 p-6">
                    <!-- Success Message -->
                    @session('status')
                        <div x-data="{ showStatusMessage: true }" x-show="showStatusMessage"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0 transform -translate-y-2"
                            x-transition:enter-end="opacity-100 transform translate-y-0"
                            x-transition:leave="transition ease-in duration-300"
                            x-transition:leave-start="opacity-100 transform translate-y-0"
                            x-transition:leave-end="opacity-0 transform -translate-y-2"
                            class="mb-6 bg-saffron-50 dark:bg-navy-800 border-l-4 border-saffron-600 p-4 rounded-md">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-saffron-600 dark:text-gold-400"
                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-navy-900 dark:text-cream-100">{{ session('status') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <div class="-mx-1.5 -my-1.5">
                                        <button @click="showStatusMessage = false"
                                            class="inline-flex rounded-md p-1.5 text-saffron-600 dark:text-gold-400 hover:bg-saffron-50 dark:hover:bg-navy-900 focus:outline-none">
                                            <span class="sr-only">{{ __('Dismiss') }}</span>
                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endsession

                    @session('password_reset')
                        <div class="mb-6 bg-saffron-50 dark:bg-navy-800 border-l-4 border-saffron-600 p-4 rounded-md print:hidden">
                            <p class="text-sm font-semibold text-navy-900 dark:text-cream-100">Password baru (tampil sekali)</p>
                            <p class="mt-2 font-mono text-lg tracking-wide text-navy-900 dark:text-gold-400 select-all">{{ session('password_reset') }}</p>
                            <p class="mt-1 text-xs text-navy-800/70 dark:text-cream-100/70">Salin sekarang. Setelah halaman di-refresh, password ini tidak ditampilkan lagi.</p>
                        </div>
                    @endsession

                    {{ $slot }}

                </div>
                
                <x-layouts.app.footer />
            </main>
        </div>
    </div>
    @include('partials.select2')
</body>

</html>
