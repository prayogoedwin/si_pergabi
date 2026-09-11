<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SettingService
{
    public const TIPE_LANGSUNG = 'langsung';

    public const TIPE_VERIFIKASI = 'verifikasi';

    public const KANAL_EMAIL = 'email';

    public const KANAL_WHATSAPP = 'whatsapp';

    public const CACHE_KEY = 'pergabi.settings';

    /**
     * @var list<string>
     */
    private const ENCRYPTED = [
        'whatsapp.fonnte_token',
        'mail.password',
    ];

    /**
     * @return array<string, string>
     */
    public static function tipeOptions(): array
    {
        return [
            self::TIPE_LANGSUNG => 'Langsung terdaftar',
            self::TIPE_VERIFIKASI => 'Dengan verifikasi email / WhatsApp',
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->all()[$key] ?? null;

        if ($value === null || $value === '') {
            return $default;
        }

        if (in_array($key, self::ENCRYPTED, true)) {
            try {
                return Crypt::decryptString($value);
            } catch (Throwable) {
                return $default;
            }
        }

        return $value;
    }

    public function boolean(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        if ($value === null) {
            return $default;
        }

        return in_array((string) $value, ['1', 'true', 'on', 'yes'], true);
    }

    public function set(string $key, mixed $value): void
    {
        if (in_array($key, self::ENCRYPTED, true) && filled($value)) {
            $value = Crypt::encryptString((string) $value);
        }

        Setting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value],
        );

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::ENCRYPTED, true) && ($value === null || $value === '')) {
                continue;
            }

            $this->set($key, $value);
        }
    }

    public function tipe(): string
    {
        $tipe = $this->get('pendaftaran.tipe', self::TIPE_VERIFIKASI);

        return in_array($tipe, [self::TIPE_LANGSUNG, self::TIPE_VERIFIKASI], true)
            ? $tipe
            : self::TIPE_VERIFIKASI;
    }

    public function isLangsung(): bool
    {
        return $this->tipe() === self::TIPE_LANGSUNG;
    }

    public function perluVerifikasi(): bool
    {
        return $this->tipe() === self::TIPE_VERIFIKASI;
    }

    public function emailAktif(): bool
    {
        return $this->boolean('verifikasi.email_aktif', true);
    }

    public function whatsappAktif(): bool
    {
        return $this->boolean('verifikasi.whatsapp_aktif', false);
    }

    public function fonnteToken(): ?string
    {
        $token = $this->get('whatsapp.fonnte_token');

        return filled($token) ? (string) $token : null;
    }

    /**
     * @return list<string>
     */
    public function kanalTersedia(): array
    {
        if (! $this->perluVerifikasi()) {
            return [];
        }

        $kanal = [];

        if ($this->emailAktif()) {
            $kanal[] = self::KANAL_EMAIL;
        }

        if ($this->whatsappAktif()) {
            $kanal[] = self::KANAL_WHATSAPP;
        }

        return $kanal;
    }

    public function harusPilihKanal(): bool
    {
        return count($this->kanalTersedia()) > 1;
    }

    public function kanalDefault(): ?string
    {
        $kanal = $this->kanalTersedia();

        return $kanal[0] ?? null;
    }

    public function resolveKanal(?string $requested): string
    {
        $tersedia = $this->kanalTersedia();

        if ($tersedia === []) {
            throw new \InvalidArgumentException('Tidak ada kanal verifikasi yang aktif.');
        }

        if (count($tersedia) === 1) {
            return $tersedia[0];
        }

        if (in_array($requested, $tersedia, true)) {
            return $requested;
        }

        throw new \InvalidArgumentException('Pilih kanal verifikasi.');
    }

    /**
     * @return array<string, mixed>
     */
    public function wizardConfig(): array
    {
        return [
            'tipe' => $this->tipe(),
            'langsung' => $this->isLangsung(),
            'emailAktif' => $this->emailAktif(),
            'whatsappAktif' => $this->whatsappAktif(),
            'pilihKanal' => $this->harusPilihKanal(),
            'defaultKanal' => $this->kanalDefault(),
        ];
    }

    public function namaLengkap(): string
    {
        return (string) $this->get('organisasi.nama_lengkap', (string) config('pergabi.nama_lengkap'));
    }

    public function singkatan(): string
    {
        return (string) $this->get('organisasi.singkatan', (string) config('pergabi.singkatan'));
    }

    public function alamat(): string
    {
        return (string) $this->get('organisasi.alamat', (string) config('pergabi.alamat'));
    }

    public function namaKetuaUmum(): string
    {
        return (string) $this->get('organisasi.nama_ketua_umum', (string) config('pergabi.nama_ketua_umum'));
    }

    public function namaSekretarisJenderal(): string
    {
        return (string) $this->get('organisasi.nama_sekretaris_jenderal', (string) config('pergabi.nama_sekretaris_jenderal'));
    }

    public function visi(): string
    {
        return (string) $this->get('organisasi.visi', (string) config('pergabi.visi'));
    }

    public function misi(): string
    {
        return (string) $this->get('organisasi.misi', (string) config('pergabi.misi'));
    }

    public function logoUrl(): string
    {
        return $this->organisasiFileUrl('organisasi.logo', 'logo');
    }

    public function stempelUrl(): string
    {
        return $this->organisasiFileUrl('organisasi.stempel', 'stempel');
    }

    public function ttdKetuaUmumUrl(): string
    {
        return $this->organisasiFileUrl('organisasi.ttd_ketua_umum', 'ttd_ketua_umum');
    }

    public function ttdSekretarisJenderalUrl(): string
    {
        return $this->organisasiFileUrl('organisasi.ttd_sekretaris_jenderal', 'ttd_sekretaris_jenderal');
    }

    /**
     * @return array<string, mixed>
     */
    public function identitas(): array
    {
        return [
            'nama_lengkap' => $this->namaLengkap(),
            'singkatan' => $this->singkatan(),
            'alamat' => $this->alamat(),
            'nama_ketua_umum' => $this->namaKetuaUmum(),
            'nama_sekretaris_jenderal' => $this->namaSekretarisJenderal(),
            'visi' => $this->visi(),
            'misi' => $this->misi(),
            'logo_url' => $this->logoUrl(),
            'stempel_url' => $this->stempelUrl(),
            'ttd_ketua_umum_url' => $this->ttdKetuaUmumUrl(),
            'ttd_sekretaris_jenderal_url' => $this->ttdSekretarisJenderalUrl(),
            'kta_halaman_depan_url' => $this->ktaHalamanDepanUrl(),
            'kta_halaman_belakang_url' => $this->ktaHalamanBelakangUrl(),
        ];
    }

    public function ktaHalamanDepanUrl(): string
    {
        return asset((string) config('pergabi.kta.halaman_depan'));
    }

    public function ktaHalamanBelakangUrl(): string
    {
        return asset((string) config('pergabi.kta.halaman_belakang'));
    }

    /**
     * @return array<string, mixed>
     */
    public function organisasiFormValues(): array
    {
        return [
            'nama_lengkap' => $this->namaLengkap(),
            'singkatan' => $this->singkatan(),
            'alamat' => $this->alamat(),
            'nama_ketua_umum' => $this->namaKetuaUmum(),
            'nama_sekretaris_jenderal' => $this->namaSekretarisJenderal(),
            'visi' => $this->visi(),
            'misi' => $this->misi(),
            'logo_url' => $this->logoUrl(),
            'stempel_url' => $this->stempelUrl(),
            'ttd_ketua_umum_url' => $this->ttdKetuaUmumUrl(),
            'ttd_sekretaris_jenderal_url' => $this->ttdSekretarisJenderalUrl(),
            'kta_halaman_depan_url' => $this->ktaHalamanDepanUrl(),
            'kta_halaman_belakang_url' => $this->ktaHalamanBelakangUrl(),
            'logo_custom' => $this->isCustomPublicFile('organisasi.logo'),
            'stempel_custom' => $this->isCustomPublicFile('organisasi.stempel'),
            'ttd_ketua_umum_custom' => $this->isCustomPublicFile('organisasi.ttd_ketua_umum'),
            'ttd_sekretaris_jenderal_custom' => $this->isCustomPublicFile('organisasi.ttd_sekretaris_jenderal'),
        ];
    }

    public function storePublicFile(string $key, UploadedFile $file): void
    {
        $old = $this->get($key);
        $name = str_replace('.', '-', $key).'-'.uniqid();
        $path = $file->storeAs('organisasi', $name.'.'.$file->getClientOriginalExtension(), 'public');

        $this->set($key, $path);

        if (filled($old) && $old !== $path && $this->isStoredUpload((string) $old)) {
            Storage::disk('public')->delete((string) $old);
        }
    }

    public function forgetPublicFile(string $key): void
    {
        $old = $this->get($key);

        if (filled($old) && $this->isStoredUpload((string) $old)) {
            Storage::disk('public')->delete((string) $old);
        }

        $this->set($key, $this->defaultFilePath($key));
    }

    public function isCustomPublicFile(string $key): bool
    {
        $path = $this->get($key);

        return filled($path) && $this->isStoredUpload((string) $path);
    }

    private function organisasiFileUrl(string $key, string $fileKey): string
    {
        $path = $this->get($key);

        if ($this->isStoredUpload((string) $path) && Storage::disk('public')->exists((string) $path)) {
            return Storage::disk('public')->url((string) $path);
        }

        $relative = $this->isDefaultPublicPath((string) $path)
            ? (string) $path
            : $this->defaultFilePath($key) ?? (string) config("pergabi.files.{$fileKey}");

        return $this->versionedAsset((string) $relative);
    }

    private function versionedAsset(string $relative): string
    {
        $full = public_path($relative);
        $url = asset($relative);

        if (is_file($full)) {
            return $url.'?v='.filemtime($full);
        }

        return $url;
    }

    private function defaultFilePath(string $key): ?string
    {
        $map = [
            'organisasi.logo' => 'logo',
            'organisasi.stempel' => 'stempel',
            'organisasi.ttd_ketua_umum' => 'ttd_ketua_umum',
            'organisasi.ttd_sekretaris_jenderal' => 'ttd_sekretaris_jenderal',
        ];

        $fileKey = $map[$key] ?? null;

        if ($fileKey === null) {
            return null;
        }

        return (string) config("pergabi.files.{$fileKey}");
    }

    private function isStoredUpload(?string $path): bool
    {
        return filled($path) && ! $this->isDefaultPublicPath($path);
    }

    private function isDefaultPublicPath(?string $path): bool
    {
        return filled($path) && str_starts_with($path, 'images/');
    }

    public function applyMailConfig(): void
    {
        $mailer = $this->get('mail.mailer');

        if (filled($mailer)) {
            Config::set('mail.default', $mailer);
        }

        $host = $this->get('mail.host');
        if (filled($host)) {
            Config::set('mail.mailers.smtp.host', $host);
        }

        $port = $this->get('mail.port');
        if (filled($port)) {
            Config::set('mail.mailers.smtp.port', (int) $port);
        }

        $username = $this->get('mail.username');
        if (filled($username)) {
            Config::set('mail.mailers.smtp.username', $username);
        }

        $password = $this->get('mail.password');
        if (filled($password)) {
            Config::set('mail.mailers.smtp.password', $password);
        }

        $scheme = $this->get('mail.scheme');
        if (filled($scheme)) {
            Config::set('mail.mailers.smtp.scheme', $scheme);
        }

        $fromAddress = $this->get('mail.from_address');
        if (filled($fromAddress)) {
            Config::set('mail.from.address', $fromAddress);
        }

        $fromName = $this->get('mail.from_name');
        if (filled($fromName)) {
            Config::set('mail.from.name', $fromName);
        }
    }

    /**
     * @return array<string, string|null>
     */
    public function formValues(): array
    {
        return [
            'pendaftaran_tipe' => $this->tipe(),
            'email_aktif' => $this->emailAktif(),
            'whatsapp_aktif' => $this->whatsappAktif(),
            'fonnte_token_tersimpan' => filled($this->fonnteToken()),
            'mail_mailer' => $this->get('mail.mailer', (string) config('mail.default')),
            'mail_host' => $this->get('mail.host', (string) config('mail.mailers.smtp.host')),
            'mail_port' => $this->get('mail.port', (string) config('mail.mailers.smtp.port')),
            'mail_username' => $this->get('mail.username', (string) config('mail.mailers.smtp.username')),
            'mail_password_tersimpan' => filled($this->get('mail.password')) || filled(config('mail.mailers.smtp.password')),
            'mail_scheme' => $this->get('mail.scheme', (string) config('mail.mailers.smtp.scheme')),
            'mail_from_address' => $this->get('mail.from_address', (string) config('mail.from.address')),
            'mail_from_name' => $this->get('mail.from_name', (string) config('mail.from.name')),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function all(): array
    {
        if (! $this->tableReady()) {
            return [];
        }

        return Cache::remember(self::CACHE_KEY, 60, function () {
            return Setting::query()->pluck('value', 'key')->all();
        });
    }

    private function tableReady(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (Throwable) {
            return false;
        }
    }
}
