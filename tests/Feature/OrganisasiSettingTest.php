<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\OrganisasiSettingSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrganisasiSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');
    }

    public function test_seeder_fills_default_identity_and_asset_paths(): void
    {
        $this->seed(OrganisasiSettingSeeder::class);

        $settings = app(SettingService::class);

        $this->assertSame('PERKUMPULAN GURU AGAMA BUDDHA INDONESIA', $settings->namaLengkap());
        $this->assertSame('PERGABI', $settings->singkatan());
        $this->assertSame('Jalan Kapuk Raya, Gang Mawar SCB RT 11 RW 01, Cengkareng, Jakarta Barat', $settings->alamat());
        $this->assertSame('Sukiman', $settings->namaKetuaUmum());
        $this->assertSame('Roch Aksiadi', $settings->namaSekretarisJenderal());
        $this->assertSame('Terwujudnya Pendidikan Agama Buddha Indonesia yang unggul, literat, dan berkarakter', $settings->visi());
        $this->assertSame('-', $settings->misi());
        $this->assertStringContainsString('images/organisasi/logo.png', $settings->logoUrl());
        $this->assertStringContainsString('images/organisasi/stempel.png', $settings->stempelUrl());
        $this->assertSame(
            'Telah disetujui dan disahkan oleh Ketua Umum PP Pergabi pada tanggal',
            $settings->teksQrKetuaUmum(),
        );
        $this->assertSame(
            'Telah disetujui oleh Sekretaris Jenderal PP Pergabi pada tanggal',
            $settings->teksQrSekretarisJenderal(),
        );
        $this->assertFalse($settings->isCustomPublicFile('organisasi.logo'));
    }

    public function test_super_admin_can_update_organisasi_identity(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('settings.organisasi.edit'))
            ->assertOk()
            ->assertSee('Identitas organisasi')
            ->assertSee('Contoh tampilan')
            ->assertSee('Halaman depan')
            ->assertSee('Halaman belakang')
            ->assertSee('assets/kta/kiri-atas.png')
            ->assertSee('assets/kta/kanan-atas.png')
            ->assertSee('assets/kta/logo-for-bg.png')
            ->assertSee('assets/kta/bendera.png')
            ->assertDontSee('halaman-depan.png')
            ->assertDontSee('halaman-belakang.png')
            ->assertSee('Stempel')
            ->assertSee('Teks QR pengesahan')
            ->assertSee('Nama Ketua Umum');

        $this->actingAs($this->superAdmin())
            ->put(route('settings.organisasi.update'), [
                'nama_lengkap' => 'PERKUMPULAN GURU AGAMA BUDDHA INDONESIA',
                'singkatan' => 'PERGABI',
                'alamat' => 'Jalan Kapuk Raya, Gang Mawar SCB RT 11 RW 01, Cengkareng, Jakarta Barat',
                'nama_ketua_umum' => 'Sukiman',
                'nama_sekretaris_jenderal' => 'Roch Aksiadi',
                'visi' => 'Visi uji kartu cetak.',
                'misi' => '-',
                'logo' => UploadedFile::fake()->image('logo.png'),
                'stempel' => UploadedFile::fake()->image('stempel.png'),
                'ttd_ketua_umum' => 'Telah disetujui dan disahkan oleh Ketua Umum PP Pergabi pada tanggal',
                'ttd_sekretaris_jenderal' => 'Telah disetujui oleh Sekretaris Jenderal PP Pergabi pada tanggal',
            ])
            ->assertRedirect();

        $settings = app(SettingService::class);

        $this->assertSame('PERKUMPULAN GURU AGAMA BUDDHA INDONESIA', $settings->namaLengkap());
        $this->assertSame('Sukiman', $settings->namaKetuaUmum());
        $this->assertSame('Roch Aksiadi', $settings->namaSekretarisJenderal());
        $this->assertSame('Visi uji kartu cetak.', $settings->visi());
        $this->assertStringContainsString('storage/organisasi', $settings->logoUrl());
        $this->assertStringContainsString('storage/organisasi', $settings->stempelUrl());
        $this->assertTrue($settings->isCustomPublicFile('organisasi.logo'));
        $this->assertSame(
            'Telah disetujui dan disahkan oleh Ketua Umum PP Pergabi pada tanggal',
            $settings->teksQrKetuaUmum(),
        );
    }

    public function test_ttd_file_path_falls_back_to_qr_text_and_can_be_updated(): void
    {
        $this->seed(OrganisasiSettingSeeder::class);

        $settings = app(SettingService::class);
        $settings->set('organisasi.ttd_ketua_umum', 'images/organisasi/ttd-ketua-umum.png');
        $settings->set('organisasi.ttd_sekretaris_jenderal', 'organisasi/ttd-sekjen.png');

        $this->assertSame(
            'Telah disetujui dan disahkan oleh Ketua Umum PP Pergabi pada tanggal',
            $settings->teksQrKetuaUmum(),
        );
        $this->assertSame(
            'Telah disetujui oleh Sekretaris Jenderal PP Pergabi pada tanggal',
            $settings->teksQrSekretarisJenderal(),
        );

        $this->actingAs($this->superAdmin())
            ->put(route('settings.organisasi.update'), [
                'nama_lengkap' => 'PERKUMPULAN GURU AGAMA BUDDHA INDONESIA',
                'singkatan' => 'PERGABI',
                'alamat' => 'Jalan Kapuk Raya, Gang Mawar SCB RT 11 RW 01, Cengkareng, Jakarta Barat',
                'nama_ketua_umum' => 'Sukiman',
                'nama_sekretaris_jenderal' => 'Roch Aksiadi',
                'visi' => 'Terwujudnya Pendidikan Agama Buddha Indonesia yang unggul, literat, dan berkarakter',
                'misi' => '-',
                'ttd_ketua_umum' => 'Disahkan Ketua Umum pada tanggal',
                'ttd_sekretaris_jenderal' => 'Disetujui Sekretaris Jenderal pada tanggal',
            ])
            ->assertRedirect();

        $settings = app(SettingService::class);

        $this->assertSame('Disahkan Ketua Umum pada tanggal', $settings->teksQrKetuaUmum());
        $this->assertSame('Disetujui Sekretaris Jenderal pada tanggal', $settings->teksQrSekretarisJenderal());
        $this->assertSame(
            'Disahkan Ketua Umum pada tanggal 22 September 2026',
            $settings->payloadQrKetuaUmum('22 September 2026'),
        );
    }

    public function test_removing_upload_restores_default_asset_path(): void
    {
        $this->seed(OrganisasiSettingSeeder::class);

        $this->actingAs($this->superAdmin())
            ->put(route('settings.organisasi.update'), [
                'nama_lengkap' => 'PERKUMPULAN GURU AGAMA BUDDHA INDONESIA',
                'singkatan' => 'PERGABI',
                'alamat' => 'Jalan Kapuk Raya, Gang Mawar SCB RT 11 RW 01, Cengkareng, Jakarta Barat',
                'nama_ketua_umum' => 'Sukiman',
                'nama_sekretaris_jenderal' => 'Roch Aksiadi',
                'visi' => 'Terwujudnya Pendidikan Agama Buddha Indonesia yang unggul, literat, dan berkarakter',
                'misi' => '-',
                'logo' => UploadedFile::fake()->image('logo.png'),
                'ttd_ketua_umum' => 'Telah disetujui dan disahkan oleh Ketua Umum PP Pergabi pada tanggal',
                'ttd_sekretaris_jenderal' => 'Telah disetujui oleh Sekretaris Jenderal PP Pergabi pada tanggal',
            ])
            ->assertRedirect();

        $this->actingAs($this->superAdmin())
            ->put(route('settings.organisasi.update'), [
                'nama_lengkap' => 'PERKUMPULAN GURU AGAMA BUDDHA INDONESIA',
                'singkatan' => 'PERGABI',
                'alamat' => 'Jalan Kapuk Raya, Gang Mawar SCB RT 11 RW 01, Cengkareng, Jakarta Barat',
                'nama_ketua_umum' => 'Sukiman',
                'nama_sekretaris_jenderal' => 'Roch Aksiadi',
                'visi' => 'Terwujudnya Pendidikan Agama Buddha Indonesia yang unggul, literat, dan berkarakter',
                'misi' => '-',
                'logo_hapus' => '1',
                'ttd_ketua_umum' => 'Telah disetujui dan disahkan oleh Ketua Umum PP Pergabi pada tanggal',
                'ttd_sekretaris_jenderal' => 'Telah disetujui oleh Sekretaris Jenderal PP Pergabi pada tanggal',
            ])
            ->assertRedirect();

        $settings = app(SettingService::class);

        $this->assertStringContainsString('images/organisasi/logo.png', $settings->logoUrl());
        $this->assertFalse($settings->isCustomPublicFile('organisasi.logo'));
    }

    public function test_admin_pp_can_access_organisasi_settings(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('settings.organisasi.edit'))
            ->assertOk()
            ->assertSee('Identitas organisasi');
    }

    private function superAdmin(): User
    {
        return User::query()->where('email', 'superadmin@example.com')->firstOrFail();
    }
}
