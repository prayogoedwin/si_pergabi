<!-- Header -->
<header class="bg-[#f7f3ee] dark:bg-navy-950 shadow-sm z-20 border-b border-[#e4ddd3] dark:border-gold-400/30">
    <div class="flex items-center justify-between h-16 px-4">
        <div class="flex items-center">
            <button @click="toggleSidebar"
                class="p-2 rounded-md text-navy-800/80 hover:text-navy-900 dark:text-cream-100/80 dark:hover:text-gold-400 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <a href="{{ route('dashboard') }}" class="ml-3 flex items-center gap-3">
                <img src="{{ asset('images/logo-pergabi.png') }}" alt="Logo PERGABI" class="h-10 w-10 object-contain">
                <span class="font-display text-2xl tracking-[0.16em] text-navy-800 dark:text-gold-400">PERGABI</span>
            </a>
        </div>

        <div class="flex items-center space-x-4">
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open"
                    class="p-2 rounded-md text-navy-800/80 hover:text-navy-900 dark:text-cream-100/80 dark:hover:text-gold-400 focus:outline-none transition-colors duration-200">
                    <svg x-show="localStorage.theme !== 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z" />
                    </svg>
                    <svg x-show="localStorage.theme === 'dark'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" x-transition
                    class="absolute border border-[#e4ddd3] dark:border-navy-800 right-0 mt-2 w-36 bg-white dark:bg-navy-800 rounded-md shadow-lg py-1 z-50">
                    <form id="header-appearance-form" action="{{ route('settings.appearance.update') }}" method="POST"
                        class="hidden">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="theme_preference" id="header_theme_preference">
                    </form>

                    <button type="button" onclick="persistTheme('light')"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-cream-50 dark:hover:bg-navy-900 flex items-center {{ (auth()->user()->theme_preference ?? 'system') === 'light' ? 'bg-cream-50 text-navy-800 dark:bg-navy-900 dark:text-gold-400 font-medium' : 'text-navy-800 dark:text-cream-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z" />
                        </svg>
                        Light
                    </button>
                    <button type="button" onclick="persistTheme('dark')"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-cream-50 dark:hover:bg-navy-900 flex items-center {{ (auth()->user()->theme_preference ?? 'system') === 'dark' ? 'bg-cream-50 text-navy-800 dark:bg-navy-900 dark:text-gold-400 font-medium' : 'text-navy-800 dark:text-cream-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        Dark
                    </button>
                    <button type="button" onclick="persistTheme('system')"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-cream-50 dark:hover:bg-navy-900 flex items-center {{ (auth()->user()->theme_preference ?? 'system') === 'system' ? 'bg-cream-50 text-navy-800 dark:bg-navy-900 dark:text-gold-400 font-medium' : 'text-navy-800 dark:text-cream-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        System
                    </button>
                </div>

                <script>
                    window.persistTheme = function(theme) {
                        if (typeof window.setAppearance === 'function') {
                            window.setAppearance(theme);
                        }

                        const form = document.getElementById('header-appearance-form');
                        const input = document.getElementById('header_theme_preference');
                        if (form && input) {
                            input.value = theme;
                            form.submit();
                        }
                    }
                </script>
            </div>
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="flex items-center focus:outline-none text-navy-800 dark:text-cream-100">
                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                        <span
                            class="flex h-full w-full items-center justify-center rounded-lg bg-navy-800 text-white dark:bg-saffron-600">
                            {{ Auth::user()->initials() }}
                        </span>
                    </span>
                    <span class="ml-2 hidden md:block">{{ Auth::user()->name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-1 text-navy-800/50 dark:text-gold-400" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="open" @click.away="open = false" :class="{ 'block': open, 'hidden': !open }"
                    class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-navy-800 rounded-md shadow-lg py-1 z-50 border border-[#e4ddd3] dark:border-gold-400/20">
                    <a href="{{ route('settings.profile.edit') }}"
                        class="block px-4 py-2 text-sm text-navy-800 dark:text-cream-100 hover:bg-cream-50 dark:hover:bg-navy-900 dark:hover:text-gold-400">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </div>
                    </a>
                    <div class="border-t border-[#e4ddd3] dark:border-gold-400/20"></div>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                            class="block w-full px-4 py-2 text-sm text-navy-800 dark:text-cream-100 hover:bg-cream-50 dark:hover:bg-navy-900 dark:hover:text-gold-400">
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
