<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Pengaturan pendaftaran</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Tipe pendaftaran, kanal verifikasi, email, dan token WhatsApp Fonnte.</p>
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

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-4">Email</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Mailer</label>
                    <select name="mail_mailer" class="w-full" data-search="off">
                        @foreach (['smtp' => 'SMTP', 'log' => 'Log (uji lokal)', 'sendmail' => 'Sendmail'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('mail_mailer', $values['mail_mailer']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Host</label>
                    <input name="mail_host" value="{{ old('mail_host', $values['mail_host']) }}" class="w-full rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Port</label>
                    <input name="mail_port" value="{{ old('mail_port', $values['mail_port']) }}" class="w-full rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Enkripsi / scheme</label>
                    <select name="mail_scheme" class="w-full" data-search="off">
                        <option value="">Tidak ada</option>
                        @foreach (['tls' => 'TLS', 'smtps' => 'SMTPS'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('mail_scheme', $values['mail_scheme']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Username</label>
                    <input name="mail_username" value="{{ old('mail_username', $values['mail_username']) }}" class="w-full rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" name="mail_password" placeholder="{{ $values['mail_password_tersimpan'] ? 'Tersimpan. Kosongkan jika tidak diubah.' : '' }}" class="w-full rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email pengirim</label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $values['mail_from_address']) }}" class="w-full rounded-lg">
                    @error('mail_from_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nama pengirim</label>
                    <input name="mail_from_name" value="{{ old('mail_from_name', $values['mail_from_name']) }}" class="w-full rounded-lg">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-4">WhatsApp (Fonnte)</h2>
            <p class="text-sm text-gray-500 mb-4">Token API dari <a href="https://fonnte.com" class="text-blue-600 hover:underline" target="_blank" rel="noreferrer">Fonnte</a>. Dipakai untuk OTP verifikasi.</p>
            <label class="block text-sm font-medium mb-1">API token</label>
            <input type="password" name="fonnte_token" placeholder="{{ $values['fonnte_token_tersimpan'] ? 'Tersimpan. Kosongkan jika tidak diubah.' : 'Token Fonnte' }}" class="w-full rounded-lg">
            @error('fonnte_token')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <button type="submit" class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">Simpan pengaturan</button>
        </div>
    </form>
</x-layouts.app>
