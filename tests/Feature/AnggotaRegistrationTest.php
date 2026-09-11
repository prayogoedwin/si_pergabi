<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use App\Services\SettingService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\WilayahSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AnggotaRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(WilayahSeeder::class);
        Storage::fake('local');
    }

    public function test_public_wizard_is_available(): void
    {
        $this->get(route('daftar'))
            ->assertOk()
            ->assertSee('Formulir pendaftaran')
            ->assertSee('tersimpan otomatis')
            ->assertSee('pendaftaran-wizard.js');

        $this->get(route('register'))->assertOk();
    }

    public function test_public_wilayah_endpoint_lists_children(): void
    {
        $this->getJson(route('daftar.wilayah'))
            ->assertOk()
            ->assertJsonFragment(['kode' => '36', 'nama' => 'Banten']);

        $this->getJson(route('daftar.wilayah', ['parent' => '36']))
            ->assertOk()
            ->assertJsonFragment(['kode' => '36.71', 'nama' => 'Kota Tangerang']);
    }

    public function test_registration_creates_anggota_account_and_email_status(): void
    {
        Notification::fake();

        $response = $this->postJson(route('daftar.store'), $this->payload());

        $response->assertCreated()
            ->assertJsonPath('redirect', route('verification.notice'));

        $user = User::query()->where('email', 'guru@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(Role::ANGGOTA));
        $this->assertFalse($user->hasVerifiedEmail());
        $this->assertAuthenticatedAs($user);

        $anggota = $user->anggota;
        $this->assertNotNull($anggota);
        $this->assertSame('3201010101010001', $anggota->nik);
        $this->assertSame(Anggota::STATUS_BELUM_VERIFIKASI_EMAIL, $anggota->status);
        $this->assertSame('36', $anggota->pd_kode);
        $this->assertSame('36.71', $anggota->pc_kode);
        $this->assertNull($anggota->nomor_anggota);
        $this->assertSame(4, $anggota->dokumen()->count());
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_ke' => Anggota::STATUS_BELUM_VERIFIKASI_EMAIL,
        ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_email_verification_advances_status_to_menunggu_pc(): void
    {
        Notification::fake();
        $this->postJson(route('daftar.store'), $this->payload());

        $user = User::query()->where('email', 'guru@example.com')->firstOrFail();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($url)
            ->assertRedirect(route('portal.show', absolute: false).'?verified=1');

        $anggota = $user->fresh()->anggota;
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame(Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, $anggota->status);
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_dari' => Anggota::STATUS_BELUM_VERIFIKASI_EMAIL,
            'status_ke' => Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
        ]);
    }

    public function test_pc_pd_pp_pipeline_writes_history_and_nomor_anggota(): void
    {
        Notification::fake();
        $this->postJson(route('daftar.store'), $this->payload());
        $user = User::query()->where('email', 'guru@example.com')->firstOrFail();
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );
        $this->actingAs($user)->get($url);

        $anggota = $user->fresh()->anggota;

        $this->actingAs($this->adminPc())
            ->post(route('anggota.verify-pc', $anggota), ['alasan' => 'Dokumen lengkap dan sesuai PC.'])
            ->assertRedirect();
        $this->assertSame(Anggota::STATUS_MENUNGGU_VALIDASI_PD, $anggota->fresh()->status);

        $this->actingAs($this->adminPd())
            ->post(route('anggota.validate-pd', $anggota), ['alasan' => 'Data divalidasi PD Banten.'])
            ->assertRedirect();
        $this->assertSame(Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP, $anggota->fresh()->status);

        $this->actingAs($this->adminPp())
            ->post(route('anggota.approve-pp', $anggota), ['alasan' => 'Disetujui Pengurus Pusat.'])
            ->assertRedirect();

        $anggota->refresh();
        $this->assertSame(Anggota::STATUS_AKTIF, $anggota->status);
        $this->assertSame(now()->year.'.36.3671.001', $anggota->nomor_anggota);
        $this->assertNotNull($anggota->tanggal_bergabung);

        $this->actingAs($user->fresh())
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('Aktif')
            ->assertSee('Disetujui Pengurus Pusat.')
            ->assertSee($anggota->nomor_anggota);
    }

    public function test_admin_pc_cannot_approve_pp(): void
    {
        Notification::fake();
        $this->postJson(route('daftar.store'), $this->payload());
        $user = User::query()->where('email', 'guru@example.com')->firstOrFail();
        $this->actingAs($user)->get(URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        ));

        $this->actingAs($this->adminPc())
            ->post(route('anggota.approve-pp', $user->anggota), ['alasan' => 'Tidak boleh.'])
            ->assertForbidden();
    }

    public function test_verification_does_not_require_alasan_but_reject_does(): void
    {
        app(SettingService::class)->putMany([
            'pendaftaran.tipe' => SettingService::TIPE_LANGSUNG,
        ]);

        $this->postJson(route('daftar.store'), $this->payload());
        $anggota = User::query()->where('email', 'guru@example.com')->firstOrFail()->anggota;

        $this->actingAs($this->adminPc())
            ->post(route('anggota.verify-pc', $anggota))
            ->assertRedirect();
        $this->assertSame(Anggota::STATUS_MENUNGGU_VALIDASI_PD, $anggota->fresh()->status);
        $this->assertSame('Diverifikasi PC.', $anggota->fresh()->statusLogs()->latest('id')->first()?->alasan);

        $this->actingAs($this->adminPd())
            ->from(route('anggota.show', $anggota))
            ->post(route('anggota.reject', $anggota))
            ->assertRedirect()
            ->assertSessionHasErrors('alasan');
        $this->assertSame(Anggota::STATUS_MENUNGGU_VALIDASI_PD, $anggota->fresh()->status);
    }

    public function test_duplicate_nik_is_rejected(): void
    {
        Notification::fake();
        $this->postJson(route('daftar.store'), $this->payload())->assertCreated();
        $this->post(route('logout'));

        $this->postJson(route('daftar.store'), $this->payload([
            'email' => 'guru2@example.com',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['nik']);
    }

    public function test_langsung_registration_skips_contact_verification(): void
    {
        app(SettingService::class)->putMany([
            'pendaftaran.tipe' => SettingService::TIPE_LANGSUNG,
        ]);

        Notification::fake();

        $this->postJson(route('daftar.store'), $this->payload())
            ->assertCreated()
            ->assertJsonPath('redirect', route('portal.show'));

        $user = User::query()->where('email', 'guru@example.com')->firstOrFail();
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame(Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, $user->anggota?->status);
        Notification::assertNothingSent();
    }

    public function test_whatsapp_only_registration_sends_otp(): void
    {
        app(SettingService::class)->putMany([
            'pendaftaran.tipe' => SettingService::TIPE_VERIFIKASI,
            'verifikasi.email_aktif' => '0',
            'verifikasi.whatsapp_aktif' => '1',
            'whatsapp.fonnte_token' => 'token-uji',
        ]);

        Http::fake([
            'https://api.fonnte.com/send' => Http::response(['status' => true], 200),
        ]);

        $this->get(route('daftar'))
            ->assertOk()
            ->assertSee('"pilihKanal":false', false)
            ->assertSee('"defaultKanal":"whatsapp"', false);

        $this->postJson(route('daftar.store'), $this->payload())
            ->assertCreated()
            ->assertJsonPath('redirect', route('verification.notice'));

        $user = User::query()->where('email', 'guru@example.com')->firstOrFail();
        $this->assertSame('whatsapp', $user->anggota?->kanal_verifikasi);
        $this->assertSame(Anggota::STATUS_BELUM_VERIFIKASI_EMAIL, $user->anggota?->status);
        $this->assertFalse($user->hasVerifiedEmail());

        Http::assertSent(fn ($request) => $request->url() === 'https://api.fonnte.com/send');

        $this->actingAs($user)
            ->get(route('verification.notice'))
            ->assertOk()
            ->assertSee('Belum verifikasi WhatsApp');

        $otp = Cache::get('pergabi.otp.wa.'.$user->id.'.plain');
        $this->assertNotEmpty($otp);

        $this->actingAs($user)
            ->post(route('verification.whatsapp'), ['otp' => $otp])
            ->assertRedirect(route('portal.show', absolute: false).'?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame(Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, $user->fresh()->anggota?->status);
    }

    public function test_both_channels_offer_a_choice_on_the_wizard(): void
    {
        app(SettingService::class)->putMany([
            'pendaftaran.tipe' => SettingService::TIPE_VERIFIKASI,
            'verifikasi.email_aktif' => '1',
            'verifikasi.whatsapp_aktif' => '1',
            'whatsapp.fonnte_token' => 'token-uji',
        ]);

        $this->get(route('daftar'))
            ->assertOk()
            ->assertSee('"pilihKanal":true', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nik' => '3201010101010001',
            'nama' => 'Guru Buddha',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Buddha',
            'status_perkawinan' => 'Kawin',
            'hp' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => 'guru@example.com',
            'alamat' => 'Jl. Melati No. 1',
            'provinsi_kode' => '36',
            'kabupaten_kode' => '36.71',
            'kecamatan_kode' => '36.71.01',
            'kelurahan_kode' => '36.71.01.1001',
            'kode_pos' => '15111',
            'status_guru' => 'ASN',
            'nip' => '199001152020011001',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'password' => 'password',
            'password_confirmation' => 'password',
            'pas_foto' => UploadedFile::fake()->image('foto.jpg'),
            'ktp' => UploadedFile::fake()->image('ktp.jpg'),
            'sk_mengajar' => UploadedFile::fake()->create('sk.pdf', 120, 'application/pdf'),
            'ijazah' => UploadedFile::fake()->create('ijazah.pdf', 120, 'application/pdf'),
        ], $overrides);
    }

    private function adminPp(): User
    {
        return User::query()->where('email', 'admin@example.com')->firstOrFail();
    }

    private function adminPd(): User
    {
        return User::query()->where('email', 'admin.pd@example.com')->firstOrFail();
    }

    private function adminPc(): User
    {
        return User::query()->where('email', 'admin.pc@example.com')->firstOrFail();
    }
}
