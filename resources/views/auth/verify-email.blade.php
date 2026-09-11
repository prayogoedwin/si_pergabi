<x-layouts.auth title="Verifikasi email">
    <div class="bg-[#fff8f1] rounded-2xl shadow-xl border border-white/20 overflow-hidden">
        <div class="p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi email</h1>
                <p class="text-gray-600 mt-2 text-sm">
                    Status keanggotaan Anda saat ini: <strong>Belum verifikasi email</strong>.
                    Cek kotak masuk, lalu buka tautan verifikasi. Setelah email terverifikasi, status berubah menjadi menunggu verifikasi PC.
                </p>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="mb-4 font-medium text-sm text-green-700">
                    Tautan verifikasi baru sudah dikirim.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.store') }}">
                @csrf
                <x-button type="primary" buttonType="submit" class="w-full !bg-saffron-600 hover:!bg-saffron-700">
                    Kirim ulang email verifikasi
                </x-button>
            </form>

            <div class="text-center mt-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-saffron-600 hover:underline font-medium">Keluar</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.auth>
