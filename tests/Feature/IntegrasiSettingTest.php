<?php

namespace Tests\Feature;

use App\Mail\SmtpTestMail;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class IntegrasiSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_super_admin_can_view_and_update_integrasi_settings(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('settings.integrasi.edit'))
            ->assertOk()
            ->assertSee('Integrasi')
            ->assertSee('Email (SMTP)')
            ->assertSee('WhatsApp (Fonnte)')
            ->assertSee('Kirim email uji')
            ->assertSee('Client ID')
            ->assertDontSee('google-secret-lama');

        $this->actingAs($this->superAdmin())
            ->put(route('settings.integrasi.update'), [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => '587',
                'mail_username' => 'noreply@pergabi.test',
                'mail_password' => 'smtp-secret',
                'mail_scheme' => 'tls',
                'mail_from_address' => 'noreply@pergabi.test',
                'mail_from_name' => 'PERGABI',
                'google_client_id' => 'google-client-id',
                'google_client_secret' => 'google-secret-lama',
                'google_redirect_uri' => 'http://127.0.0.1:8000/login/google/callback',
                'fonnte_token' => 'token-fonnte',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $settings = app(SettingService::class);

        $this->assertSame('smtp', $settings->get('mail.mailer'));
        $this->assertSame('smtp.example.com', $settings->get('mail.host'));
        $this->assertSame('smtp-secret', $settings->get('mail.password'));
        $this->assertSame('google-client-id', $settings->get('google.client_id'));
        $this->assertSame('google-secret-lama', $settings->get('google.client_secret'));
        $this->assertSame('http://127.0.0.1:8000/login/google/callback', $settings->get('google.redirect_uri'));
        $this->assertSame('token-fonnte', $settings->fonnteToken());
        $this->assertTrue($settings->googleLoginEnabled());
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame('google-client-id', config('services.google.client_id'));
    }

    public function test_blank_secrets_keep_the_stored_values(): void
    {
        $settings = app(SettingService::class);
        $settings->putMany([
            'mail.password' => 'smtp-secret',
            'google.client_id' => 'google-client-id',
            'google.client_secret' => 'google-secret-lama',
            'whatsapp.fonnte_token' => 'token-fonnte',
        ]);

        $this->actingAs($this->superAdmin())
            ->put(route('settings.integrasi.update'), [
                'mail_mailer' => 'log',
                'mail_from_address' => 'noreply@pergabi.test',
                'mail_from_name' => 'PERGABI',
                'google_client_id' => 'google-client-id',
                'google_client_secret' => '',
                'google_redirect_uri' => 'http://127.0.0.1:8000/login/google/callback',
                'fonnte_token' => '',
            ])
            ->assertRedirect();

        $settings = app(SettingService::class);

        $this->assertSame('smtp-secret', $settings->get('mail.password'));
        $this->assertSame('google-secret-lama', $settings->get('google.client_secret'));
        $this->assertSame('token-fonnte', $settings->fonnteToken());
    }

    public function test_saved_google_settings_show_the_login_button(): void
    {
        $this->actingAs($this->superAdmin())
            ->put(route('settings.integrasi.update'), [
                'mail_mailer' => 'log',
                'google_client_id' => 'google-client-id',
                'google_client_secret' => 'google-client-secret',
                'google_redirect_uri' => 'http://127.0.0.1:8000/login/google/callback',
            ])
            ->assertRedirect();

        $this->post('/logout');

        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk dengan Google');
    }

    public function test_admin_pp_can_access_integrasi_settings(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('settings.integrasi.edit'))
            ->assertOk()
            ->assertSee('Email (SMTP)')
            ->assertSee('WhatsApp (Fonnte)')
            ->assertSee('Kirim email uji');
    }

    public function test_smtp_test_sends_mail_using_saved_settings(): void
    {
        Mail::fake();

        app(SettingService::class)->putMany([
            'mail.mailer' => 'smtp',
            'mail.host' => 'smtp.example.com',
            'mail.from_address' => 'noreply@pergabi.test',
            'mail.from_name' => 'PERGABI',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('settings.integrasi.test-smtp'), [
                'email' => 'uji@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        Mail::assertSent(SmtpTestMail::class, 'uji@example.com');
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
    }

    public function test_smtp_test_warns_when_mailer_is_log(): void
    {
        Mail::fake();

        app(SettingService::class)->putMany([
            'mail.mailer' => 'log',
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('settings.integrasi.test-smtp'), [
                'email' => 'uji@example.com',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', fn (string $status): bool => str_contains($status, 'Log (uji lokal)'));

        Mail::assertSent(SmtpTestMail::class, 'uji@example.com');
    }

    public function test_smtp_test_validates_destination_email(): void
    {
        $this->actingAs($this->superAdmin())
            ->from(route('settings.integrasi.edit'))
            ->post(route('settings.integrasi.test-smtp'), [
                'email' => 'bukan-email',
            ])
            ->assertRedirect(route('settings.integrasi.edit'))
            ->assertSessionHasErrors('email');
    }

    public function test_smtp_test_shows_safe_error_when_sending_fails(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new RuntimeException('Connection refused password=rahasia-smtp'));

        $this->actingAs($this->superAdmin())
            ->from(route('settings.integrasi.edit'))
            ->post(route('settings.integrasi.test-smtp'), [
                'email' => 'uji@example.com',
            ])
            ->assertRedirect(route('settings.integrasi.edit'))
            ->assertSessionHas('smtp_error')
            ->assertSessionMissing('status');

        $error = session('smtp_error');
        $this->assertIsString($error);
        $this->assertStringContainsString('Gagal mengirim email uji', $error);
        $this->assertStringNotContainsString('rahasia-smtp', $error);
    }

    public function test_admin_pd_cannot_send_smtp_test(): void
    {
        $adminPd = User::query()->where('email', 'admin.pd@example.com')->firstOrFail();

        $this->actingAs($adminPd)
            ->post(route('settings.integrasi.test-smtp'), [
                'email' => 'uji@example.com',
            ])
            ->assertForbidden();
    }

    private function superAdmin(): User
    {
        return User::query()->where('email', 'superadmin@example.com')->firstOrFail();
    }
}
