<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Pengaturan pendaftaran</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Tipe pendaftaran dan kanal verifikasi.@if (auth()->user()?->hasPermission('view-integrasi')) Token Fonnte, SMTP, dan login Google ada di menu <a href="{{ route('settings.integrasi.edit') }}" class="text-blue-600 hover:underline">Integrasi</a>.@endif</p>
    </div>

    <form method="POST" action="{{ route('settings.pendaftaran.update') }}" class="space-y-6" x-data="{ tipe: @js($values['pendaftaran_tipe']), emailAktif: @js((bool) $values['email_aktif']), waAktif: @js((bool) $values['whatsapp_aktif']) }">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-4">Tipe pendaftaran</h2>
            <div class="space-y-3">
                @foreach ($tipeOptions as $value => $label)
                    <label class="flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4 cursor-pointer">
                        <input type="radio" name="pendaftaran_tipe" value="{{ $value }}" x-model="tipe" @checked(old('pendaftaran_tipe', $values['pendaftaran_tipe']) === $value)>
                        <span>
                            <span class="font-medium">{{ $label }}</span>
                            @if ($value === 'langsung')
                                <span class="block text-sm text-gray-500 mt-1">Akun anggota langsung masuk antrian verifikasi PC, tanpa OTP email/WhatsApp.</span>
                            @else
                                <span class="block text-sm text-gray-500 mt-1">Wajib verifikasi email dan/atau WhatsApp sebelum menunggu verifikasi PC. Minimal satu kanal aktif.</span>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6" x-show="tipe === 'verifikasi'">
            <h2 class="text-lg font-semibold mb-2">Kanal verifikasi</h2>
            <p class="text-sm text-gray-500 mb-4">Jika hanya satu yang aktif, pendaftar tidak memilih. Jika keduanya aktif, pendaftar memilih email atau WhatsApp.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <span class="font-medium">Email</span>
                    <input type="hidden" name="email_aktif" value="0">
                    <input type="checkbox" name="email_aktif" value="1" x-model="emailAktif" @checked(old('email_aktif', $values['email_aktif']))>
                </label>
                <label class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <span class="font-medium">WhatsApp</span>
                    <input type="hidden" name="whatsapp_aktif" value="0">
                    <input type="checkbox" name="whatsapp_aktif" value="1" x-model="waAktif" @checked(old('whatsapp_aktif', $values['whatsapp_aktif']))>
                </label>
            </div>
        </div>

        <div>
            <button type="submit" class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">Simpan pengaturan</button>
        </div>
    </form>
</x-layouts.app>
