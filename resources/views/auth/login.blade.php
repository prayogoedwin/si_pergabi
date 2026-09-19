<x-layouts.auth title="Masuk">
    <div class="bg-[#fff8f1] rounded-2xl shadow-xl border border-white/20 overflow-hidden">
        <div class="p-7">
            <div class="mb-5 text-center">
                <h1 class="text-2xl font-bold text-slate-800">Masuk</h1>
                <p class="text-sm text-slate-500 mt-1">Sistem Informasi Keanggotaan PERGABI</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-3">
                @csrf
                <div>
                    <x-forms.input label="Email" name="email" type="email" placeholder="nama@email.com" autofocus />
                </div>

                <div>
                    <x-forms.input label="Kata sandi" name="password" type="password" placeholder="••••••••" />

                    <div class="flex items-center justify-between mt-2">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-xs text-saffron-600 hover:underline">Lupa kata sandi?</a>
                        @endif
                        <x-forms.checkbox label="Ingat saya" name="remember" />
                    </div>
                </div>

                <x-button type="primary" class="w-full !bg-saffron-600 hover:!bg-saffron-700 !py-3 !text-base">Masuk</x-button>
            </form>

            @if ($googleLogin ?? false)
                <div class="relative my-5">
                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                        <div class="w-full border-t border-slate-200"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-[#fff8f1] px-3 text-xs text-slate-400">atau</span>
                    </div>
                </div>

                @error('google')
                    <p class="mb-3 text-sm text-red-600 text-center">{{ $message }}</p>
                @enderror

                <a href="{{ route('login.google') }}"
                    class="flex items-center justify-center gap-3 w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M12 10.2v3.6h5.1c-.2 1.2-.9 2.3-1.9 3l3.1 2.4c1.8-1.7 2.9-4.1 2.9-7 0-.7-.1-1.3-.2-1.9H12z"/>
                        <path fill="#34A853" d="M12 21.6c2.6 0 4.8-.9 6.4-2.4l-3.1-2.4c-.9.6-2 .9-3.3.9-2.5 0-4.6-1.7-5.4-4l-3.2 2.5c1.6 3.2 4.9 5.4 8.6 5.4z"/>
                        <path fill="#FBBC05" d="M6.6 13.7c-.2-.6-.3-1.2-.3-1.7s.1-1.2.3-1.7L3.4 7.8C2.5 9.5 2 11.2 2 12c0 1.8.5 3.5 1.4 5l3.2-2.3z"/>
                        <path fill="#4285F4" d="M12 5.4c1.4 0 2.7.5 3.7 1.4l2.8-2.8C16.8 2.4 14.6 1.5 12 1.5 8.3 1.5 5 3.7 3.4 6.9l3.2 2.5C7.4 7.1 9.5 5.4 12 5.4z"/>
                    </svg>
                    Masuk dengan Google
                </a>
            @endif

            @if (Route::has('register'))
                <div class="text-center mt-6">
                    <p class="text-sm text-slate-600">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-saffron-600 hover:underline font-medium">Daftar</a>
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.auth>
