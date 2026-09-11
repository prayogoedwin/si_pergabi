<x-layouts.auth title="Verifikasi WhatsApp">
    <div class="bg-[#fff8f1] rounded-2xl shadow-xl border border-white/20 overflow-hidden">
        <div class="p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Verifikasi WhatsApp</h1>
                <p class="text-gray-600 mt-2 text-sm">
                    Status saat ini: <strong>Belum verifikasi WhatsApp</strong>.
                    Kode 6 digit dikirim ke {{ $whatsapp }}. Setelah benar, status menjadi menunggu verifikasi PC.
                </p>
            </div>

            @if (session('status') === 'otp-sent')
                <div class="mb-4 font-medium text-sm text-green-700">Kode baru sudah dikirim.</div>
            @endif

            @error('otp')
                <div class="mb-4 text-sm text-red-600">{{ $message }}</div>
            @enderror

            <form method="POST" action="{{ route('verification.whatsapp') }}" class="space-y-4">
                @csrf
                <input name="otp" inputmode="numeric" maxlength="6" required
                    class="w-full text-center tracking-[0.4em] text-2xl rounded-xl border-gray-300 py-3" placeholder="000000">
                <x-button type="primary" buttonType="submit" class="w-full !bg-saffron-600 hover:!bg-saffron-700">
                    Verifikasi
                </x-button>
            </form>

            <form method="POST" action="{{ route('verification.whatsapp.resend') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full text-sm text-saffron-600 hover:underline">Kirim ulang kode</button>
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
