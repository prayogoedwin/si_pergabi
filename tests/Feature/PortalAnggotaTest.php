<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PortalAnggotaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        Storage::fake('local');
    }

    public function test_pengurus_login_goes_to_dashboard(): void
    {
        foreach (['superadmin@example.com', 'admin@example.com', 'admin.pd@example.com', 'admin.pc@example.com'] as $email) {
            $this->post('/login', [
                'email' => $email,
                'password' => 'password',
            ])->assertRedirect(route('dashboard', absolute: false));

            $this->post('/logout');
        }
    }

    public function test_verified_member_is_sent_to_portal_not_dashboard(): void
    {
        $user = $this->makeMember();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('portal.show', absolute: false));

        $this->actingAs($user->fresh())
            ->get(route('dashboard'))
            ->assertRedirect(route('portal.show'));
    }

    public function test_unverified_member_cannot_open_portal(): void
    {
        $user = $this->makeMember(Anggota::STATUS_BELUM_VERIFIKASI_EMAIL, verified: false);

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_member_sees_compact_portal_without_full_kta(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('Portal anggota')
            ->assertSee('Guru Portal')
            ->assertSee('Menunggu verifikasi PC')
            ->assertSee('Tersedia setelah persetujuan PP')
            ->assertDontSee('Master')
            ->assertDontSee('User Management');

        $this->actingAs($user)
            ->get(route('portal.kta'))
            ->assertOk()
            ->assertSee('Belum dapat ditampilkan')
            ->assertDontSee('Cetak ID Card');
    }

    public function test_full_kta_is_available_after_pp_approval(): void
    {
        $user = $this->makeMember(Anggota::STATUS_AKTIF);
        $anggota = $user->anggota;

        $this->actingAs($user)
            ->get(route('portal.kta'))
            ->assertOk()
            ->assertSee('Cetak ID Card')
            ->assertSee('Cetak A4 / PDF')
            ->assertSee('Unduh PNG')
            ->assertSee('kta-demo-front', false)
            ->assertSee('kta-demo-back', false)
            ->assertSee($anggota->namaLengkap())
            ->assertSee($anggota->nomor_anggota)
            ->assertSee($anggota->nik);

        $this->get(route('kta.verifikasi', $anggota->nomor_anggota))
            ->assertOk()
            ->assertSee($anggota->namaLengkap())
            ->assertSee('Aktif')
            ->assertSee($anggota->nomor_anggota);
    }

    public function test_public_verification_shows_inactive_status(): void
    {
        $user = $this->makeMember(Anggota::STATUS_AKTIF);
        $user->anggota->update(['status' => Anggota::STATUS_TIDAK_AKTIF]);

        $this->get(route('kta.verifikasi', $user->anggota->nomor_anggota))
            ->assertOk()
            ->assertSee('Tidak aktif');
    }

    public function test_pengurus_is_redirected_away_from_portal(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('portal.show'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_member_can_update_password_from_portal(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->put(route('portal.password'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_member_can_update_photo_from_portal(): void
    {
        $user = $this->makeMember();

        $this->actingAs($user)
            ->post(route('portal.foto.update'), [
                'foto' => UploadedFile::fake()->image('baru.jpg'),
            ])
            ->assertRedirect();

        $this->assertNotNull($user->fresh()->anggota?->foto_path);
        Storage::disk('local')->assertExists($user->fresh()->anggota->foto_path);
    }

    private function makeMember(
        string $status = Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
        bool $verified = true,
    ): User {
        $user = User::factory()->create([
            'name' => 'Guru Portal',
            'email' => 'guru.portal@example.com',
            'email_verified_at' => $verified ? now() : null,
        ]);
        $user->assignRole(Role::ANGGOTA);

        $aktif = $status === Anggota::STATUS_AKTIF;

        Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => '3201010101010099',
            'nama' => 'Guru Portal',
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Buddha',
            'status_perkawinan' => 'Kawin',
            'hp' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => $user->email,
            'alamat' => 'Jl. Melati No. 1',
            'provinsi_kode' => '36',
            'kabupaten_kode' => '36.71',
            'kecamatan_kode' => '36.71.01',
            'kelurahan_kode' => '36.71.01.1001',
            'kode_pos' => '15111',
            'status_guru' => 'ASN',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pd_kode' => '36',
            'pc_kode' => '36.71',
            'status' => $status,
            'nomor_anggota' => $aktif ? now()->year.'.36.3671.099' : null,
            'tanggal_bergabung' => $aktif ? now()->toDateString() : null,
            'masa_berlaku_hingga' => $aktif ? now()->addYears(3)->toDateString() : null,
        ]);

        return $user->fresh(['anggota', 'roles']);
    }
}
