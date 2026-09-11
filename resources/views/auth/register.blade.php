<x-layouts.auth title="Daftar">
    <div class="bg-[#fff8f1] rounded-2xl shadow-xl border border-white/20 overflow-hidden">
        <div class="p-7">
            <div class="mb-5 text-center">
                <h1 class="text-2xl font-bold text-slate-800">Daftar</h1>
                <p class="text-sm text-slate-500 mt-1">Buat akun untuk keanggotaan PERGABI</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-3">
                @csrf
                <div>
                    <x-forms.input label="Nama lengkap" name="name" type="text" placeholder="Nama lengkap" autofocus />
                </div>

                <div>
                    <x-forms.input label="Email" name="email" type="email" placeholder="nama@email.com" />
                </div>

                <div>
                    <x-forms.input label="Kata sandi" name="password" type="password" placeholder="••••••••" />
                </div>

                <div>
                    <x-forms.input label="Konfirmasi kata sandi" name="password_confirmation" type="password"
                        placeholder="••••••••" />
                </div>

                <x-button type="primary" class="w-full !bg-saffron-600 hover:!bg-saffron-700 !py-3 !text-base">Daftar</x-button>
            </form>

            <div class="text-center mt-6">
                <p class="text-sm text-slate-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="text-saffron-600 hover:underline font-medium">Masuk</a>
                </p>
            </div>
        </div>
    </div>
</x-layouts.auth>
