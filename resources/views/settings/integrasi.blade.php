<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Integrasi</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">SMTP, login Google, dan token WhatsApp Fonnte. Disimpan di database, bisa diubah tanpa menyentuh file .env.</p>
    </div>

    <form id="integrasi-form" method="POST" action="{{ route('settings.integrasi.update') }}" class="hidden">
        @csrf
        @method('PUT')
    </form>

    <div class="space-y-6">
        @if ($errors->any())
            <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
        @endif

        @session('smtp_error')
            <div class="rounded-lg bg-red-50 text-red-700 px-4 py-3 text-sm">{{ session('smtp_error') }}</div>
        @endsession

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-1">Email (SMTP)</h2>
            <p class="text-sm text-gray-500 mb-4">Dipakai untuk verifikasi email pendaftaran dan reset kata sandi.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Mailer</label>
                    <select name="mail_mailer" form="integrasi-form" class="w-full" data-search="off">
                        @foreach (['smtp' => 'SMTP', 'log' => 'Log (uji lokal)', 'sendmail' => 'Sendmail'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('mail_mailer', $values['mail_mailer']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Host</label>
                    <input name="mail_host" form="integrasi-form" value="{{ old('mail_host', $values['mail_host']) }}" class="w-full rounded-lg" placeholder="smtp.gmail.com">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Port</label>
                    <input name="mail_port" form="integrasi-form" value="{{ old('mail_port', $values['mail_port']) }}" class="w-full rounded-lg" placeholder="587">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Enkripsi / scheme</label>
                    <select name="mail_scheme" form="integrasi-form" class="w-full" data-search="off">
                        <option value="">Tidak ada</option>
                        @foreach (['tls' => 'TLS', 'smtps' => 'SMTPS'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('mail_scheme', $values['mail_scheme']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Username</label>
                    <input name="mail_username" form="integrasi-form" value="{{ old('mail_username', $values['mail_username']) }}" class="w-full rounded-lg" autocomplete="off">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" name="mail_password" form="integrasi-form" placeholder="{{ $values['mail_password_tersimpan'] ? 'Tersimpan. Kosongkan jika tidak diubah.' : '' }}" class="w-full rounded-lg" autocomplete="new-password">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email pengirim</label>
                    <input type="email" name="mail_from_address" form="integrasi-form" value="{{ old('mail_from_address', $values['mail_from_address']) }}" class="w-full rounded-lg">
                    @error('mail_from_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nama pengirim</label>
                    <input name="mail_from_name" form="integrasi-form" value="{{ old('mail_from_name', $values['mail_from_name']) }}" class="w-full rounded-lg">
                </div>
            </div>

            <form method="POST" action="{{ route('settings.integrasi.test-smtp') }}" class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                @csrf
                <h3 class="text-sm font-semibold mb-1">Uji pengiriman</h3>
                <p class="text-xs text-gray-500 mb-3">Memakai pengaturan yang sudah disimpan. Simpan dulu jika baru diubah, baru kirim email uji.</p>
                <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Email tujuan</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-lg" placeholder="email@contoh.com">
                        @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-lg border border-blue-600 text-blue-700 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-navy-900 font-semibold whitespace-nowrap">Kirim email uji</button>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-1">Login Google</h2>
            <p class="text-sm text-gray-500 mb-4">
                Tombol “Masuk dengan Google” muncul setelah Client ID dan Client Secret terisi.
                Daftar tetap manual. Email yang sudah terdaftar bisa masuk dengan Google; yang belum ada diarahkan ke formulir daftar.
            </p>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Client ID</label>
                    <input name="google_client_id" form="integrasi-form" value="{{ old('google_client_id', $values['google_client_id']) }}" class="w-full rounded-lg" autocomplete="off">
                    @error('google_client_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Client Secret</label>
                    <input type="password" name="google_client_secret" form="integrasi-form" placeholder="{{ $values['google_client_secret_tersimpan'] ? 'Tersimpan. Kosongkan jika tidak diubah.' : '' }}" class="w-full rounded-lg" autocomplete="new-password">
                    @error('google_client_secret')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Redirect URI</label>
                    <input name="google_redirect_uri" form="integrasi-form" value="{{ old('google_redirect_uri', $values['google_redirect_uri']) }}" class="w-full rounded-lg" placeholder="http://127.0.0.1:8000/login/google/callback">
                    <p class="text-xs text-gray-500 mt-1">Harus sama persis dengan Authorized redirect URI di Google Cloud Console.</p>
                    @error('google_redirect_uri')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-1">WhatsApp (Fonnte)</h2>
            <p class="text-sm text-gray-500 mb-4">Token API dari <a href="https://fonnte.com" class="text-blue-600 hover:underline" target="_blank" rel="noreferrer">Fonnte</a>. Dipakai untuk OTP verifikasi. Kanal WhatsApp tetap diaktifkan di Pengaturan Pendaftaran.</p>
            <label class="block text-sm font-medium mb-1">API token</label>
            <input type="password" name="fonnte_token" form="integrasi-form" placeholder="{{ $values['fonnte_token_tersimpan'] ? 'Tersimpan. Kosongkan jika tidak diubah.' : 'Token Fonnte' }}" class="w-full rounded-lg" autocomplete="new-password">
            @error('fonnte_token')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <button type="submit" form="integrasi-form" class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">Simpan pengaturan</button>
        </div>
    </div>
</x-layouts.app>
