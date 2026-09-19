<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendaftaranSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_view_and_update_settings(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('settings.pendaftaran.edit'))
            ->assertOk()
            ->assertSee('Tipe pendaftaran')
            ->assertSee('Integrasi')
            ->assertDontSee('API token');

        app(SettingService::class)->putMany([
            'whatsapp.fonnte_token' => 'token-baru',
        ]);

        $this->actingAs($this->superAdmin())
            ->put(route('settings.pendaftaran.update'), [
                'pendaftaran_tipe' => SettingService::TIPE_VERIFIKASI,
                'email_aktif' => '1',
                'whatsapp_aktif' => '1',
            ])
            ->assertRedirect();

        $settings = app(SettingService::class);

        $this->assertSame(SettingService::TIPE_VERIFIKASI, $settings->tipe());
        $this->assertTrue($settings->emailAktif());
        $this->assertTrue($settings->whatsappAktif());
        $this->assertSame('token-baru', $settings->fonnteToken());
        $this->assertTrue($settings->harusPilihKanal());
    }

    public function test_verification_mode_requires_one_channel(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('settings.pendaftaran.update'), [
                'pendaftaran_tipe' => SettingService::TIPE_VERIFIKASI,
                'email_aktif' => '0',
                'whatsapp_aktif' => '0',
            ])
            ->assertSessionHasErrors('email_aktif');
    }

    public function test_whatsapp_channel_requires_fonnte_token(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('settings.pendaftaran.update'), [
                'pendaftaran_tipe' => SettingService::TIPE_VERIFIKASI,
                'email_aktif' => '0',
                'whatsapp_aktif' => '1',
            ])
            ->assertSessionHasErrors('whatsapp_aktif');
    }

    public function test_admin_pp_can_access_settings(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('settings.pendaftaran.edit'))
            ->assertOk()
            ->assertSee('Tipe pendaftaran');
    }

    private function superAdmin(): User
    {
        return User::query()->where('email', 'superadmin@example.com')->firstOrFail();
    }
}
