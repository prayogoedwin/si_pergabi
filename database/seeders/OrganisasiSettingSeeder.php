<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class OrganisasiSettingSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const TEXT = [
        'organisasi.nama_lengkap' => 'PERKUMPULAN GURU AGAMA BUDDHA INDONESIA',
        'organisasi.singkatan' => 'PERGABI',
        'organisasi.alamat' => 'Jalan Kapuk Raya, Gang Mawar SCB RT 11 RW 01, Cengkareng, Jakarta Barat',
        'organisasi.nama_ketua_umum' => 'Sukiman',
        'organisasi.nama_sekretaris_jenderal' => 'Roch Aksiadi',
        'organisasi.visi' => 'Terwujudnya Pendidikan Agama Buddha Indonesia yang unggul, literat, dan berkarakter',
        'organisasi.misi' => '-',
    ];

    /**
     * @var array<string, string>
     */
    private const FILES = [
        'organisasi.logo' => 'logo',
        'organisasi.stempel' => 'stempel',
        'organisasi.ttd_ketua_umum' => 'ttd_ketua_umum',
        'organisasi.ttd_sekretaris_jenderal' => 'ttd_sekretaris_jenderal',
    ];

    public function run(): void
    {
        foreach (self::TEXT as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        foreach (self::FILES as $key => $fileKey) {
            $current = Setting::query()->where('key', $key)->value('value');

            if (filled($current) && ! str_starts_with((string) $current, 'images/')) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => (string) config("pergabi.files.{$fileKey}")],
            );
        }

        Cache::forget('pergabi.settings');
    }
}
