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
