<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\AnggotaDokumen;
use App\Models\AnggotaStatusLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\WilayahSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnggotaExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(WilayahSeeder::class);
        Storage::fake('local');
    }

    public function test_admin_index_shows_masa_berlaku_and_expires_overdue_members(): void
    {
        $anggota = $this->makeAktif([
            'nama' => 'Guru Kedaluwarsa',
            'masa_berlaku_hingga' => now()->subDay()->toDateString(),
        ]);
        $logAwal = AnggotaStatusLog::query()->create([
            'anggota_id' => $anggota->id,
            'status_dari' => Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP,
            'status_ke' => Anggota::STATUS_AKTIF,
            'alasan' => 'Disetujui Pengurus Pusat.',
            'created_at' => now()->subYears(5),
        ]);

        $this->actingAs($this->adminPp())
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertSee('Masa berlaku')
            ->assertSee('Status')
            ->assertSee('Guru Kedaluwarsa')
            ->assertSee(now()->subDay()->format('d M Y'))
            ->assertSee('Habis')
            ->assertSee('Tidak aktif');

        $anggota->refresh();
        $this->assertSame(Anggota::STATUS_TIDAK_AKTIF, $anggota->status);
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_dari' => Anggota::STATUS_AKTIF,
            'status_ke' => Anggota::STATUS_TIDAK_AKTIF,
            'alasan' => 'Masa berlaku keanggotaan habis.',
        ]);
        $this->assertTrue(AnggotaStatusLog::query()->whereKey($logAwal->id)->exists());
    }

    public function test_artisan_command_deactivates_expired_members(): void
    {
        $anggota = $this->makeAktif([
            'masa_berlaku_hingga' => now()->subDay()->toDateString(),
        ]);
        $masihBerlaku = $this->makeAktif([
            'nik' => '3201010101010002',
            'email' => 'masih@example.com',
            'nomor_anggota' => now()->year.'.36.3671.002',
            'masa_berlaku_hingga' => now()->addYear()->toDateString(),
        ]);

        $this->artisan('pergabi:expire-anggota')
            ->expectsOutput('Dinonaktifkan: 1 anggota.')
            ->assertSuccessful();

        $this->assertSame(Anggota::STATUS_TIDAK_AKTIF, $anggota->fresh()->status);
        $this->assertSame(Anggota::STATUS_AKTIF, $masihBerlaku->fresh()->status);
    }

    public function test_expired_member_is_prompted_to_renew_and_kta_shows_inactive(): void
    {
        $user = $this->makeAktif([
            'masa_berlaku_hingga' => now()->subDay()->toDateString(),
        ])->user;

        $this->actingAs($user)
            ->get(route('portal.show'))
            ->assertOk()
            ->assertSee('Tidak aktif')
            ->assertSee('Pengajuan / verifikasi ulang');

        $this->actingAs($user)
            ->get(route('portal.kta'))
            ->assertOk()
            ->assertSee('Tidak aktif')
            ->assertDontSee('Cetak ID Card')
            ->assertSee('kta-demo-front', false);

        $this->actingAs($user)
            ->get(route('portal.qr'))
            ->assertOk()
            ->assertSee('Tidak aktif')
            ->assertSee('data-anggota-qr', false);

        $this->get($user->anggota->urlVerifikasiQr())
            ->assertOk()
            ->assertSee('Kartu tidak valid')
            ->assertSee('Tidak aktif');
    }

    public function test_member_renewal_keeps_history_and_restarts_verification(): void
    {
        $anggota = $this->makeAktif([
            'masa_berlaku_hingga' => now()->subDay()->toDateString(),
        ]);
        $user = $anggota->user;
        $nomor = $anggota->nomor_anggota;
        $oldFoto = $anggota->dokumen()->create([
            'jenis' => AnggotaDokumen::PAS_FOTO,
            'path' => 'anggota/old-foto.jpg',
            'nama_asli' => 'lama.jpg',
            'mime' => 'image/jpeg',
            'ukuran' => 100,
        ]);
        AnggotaStatusLog::query()->create([
            'anggota_id' => $anggota->id,
            'status_dari' => Anggota::STATUS_MENUNGGU_PERSETUJUAN_PP,
            'status_ke' => Anggota::STATUS_AKTIF,
            'alasan' => 'Disetujui Pengurus Pusat.',
            'created_at' => now()->subYears(5),
        ]);

        $this->actingAs($user)
            ->get(route('portal.perpanjang'))
            ->assertOk()
            ->assertSee('Formulir pengajuan ulang')
            ->assertSee('Kirim pengajuan ulang')
            ->assertDontSee('Formulir pendaftaran');

        $this->actingAs($user)
            ->postJson(route('portal.perpanjang.store'), $this->renewalPayload($user))
            ->assertCreated()
            ->assertJsonPath('redirect', route('portal.show'));

        $anggota->refresh();
        $this->assertSame(Anggota::STATUS_MENUNGGU_VERIFIKASI_PC, $anggota->status);
        $this->assertSame($nomor, $anggota->nomor_anggota);
        $this->assertSame('Guru Diperbarui', $anggota->nama);
        $this->assertTrue($anggota->dokumen()->whereKey($oldFoto->id)->exists());
        $this->assertGreaterThan(1, $anggota->dokumen()->where('jenis', AnggotaDokumen::PAS_FOTO)->count());
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_ke' => Anggota::STATUS_AKTIF,
            'alasan' => 'Disetujui Pengurus Pusat.',
        ]);
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_dari' => Anggota::STATUS_AKTIF,
            'status_ke' => Anggota::STATUS_TIDAK_AKTIF,
            'alasan' => 'Masa berlaku keanggotaan habis.',
        ]);
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_dari' => Anggota::STATUS_TIDAK_AKTIF,
            'status_ke' => Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
            'alasan' => 'Pengajuan ulang keanggotaan setelah masa berlaku habis.',
        ]);

        $this->actingAs($this->adminPc())
            ->post(route('anggota.verify-pc', $anggota), ['alasan' => 'Dokumen perpanjangan lengkap.'])
            ->assertRedirect();
        $this->actingAs($this->adminPd())
            ->post(route('anggota.validate-pd', $anggota), ['alasan' => 'Divalidasi PD.'])
            ->assertRedirect();
        $this->actingAs($this->adminPp())
            ->post(route('anggota.approve-pp', $anggota), ['alasan' => 'Perpanjangan disetujui PP.'])
            ->assertRedirect();

        $anggota->refresh();
        $this->assertSame(Anggota::STATUS_AKTIF, $anggota->status);
        $this->assertSame($nomor, $anggota->nomor_anggota);
        $this->assertSame(now()->addYears(5)->toDateString(), $anggota->masa_berlaku_hingga?->toDateString());
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_ke' => Anggota::STATUS_AKTIF,
            'alasan' => 'Disetujui Pengurus Pusat.',
        ]);
        $this->assertDatabaseHas('anggota_status_log', [
            'anggota_id' => $anggota->id,
            'status_ke' => Anggota::STATUS_AKTIF,
            'alasan' => 'Perpanjangan disetujui PP.',
        ]);

        $this->actingAs($user->fresh())
            ->get(route('portal.kta'))
            ->assertOk()
            ->assertSee('Cetak ID Card');
    }

    public function test_active_member_cannot_open_renewal_form(): void
    {
        $user = $this->makeAktif()->user;

        $this->actingAs($user)
            ->get(route('portal.perpanjang'))
            ->assertRedirect(route('portal.show'));
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeAktif(array $overrides = []): Anggota
    {
        $email = $overrides['email'] ?? 'guru.expire@example.com';
        $nama = $overrides['nama'] ?? 'Guru Expire';

        $user = User::factory()->create([
            'name' => $nama,
            'email' => $email,
        ]);
        $user->assignRole(Role::ANGGOTA);

        $payload = array_merge([
            'user_id' => $user->id,
            'nik' => '3201010101010001',
            'nama' => $nama,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Buddha',
            'status_perkawinan' => 'Kawin',
            'hp' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => $email,
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
            'pd_kode' => '36',
            'pc_kode' => '36.71',
            'status' => Anggota::STATUS_AKTIF,
            'nomor_anggota' => now()->year.'.36.3671.001',
            'tanggal_bergabung' => now()->subYears(5)->toDateString(),
            'masa_berlaku_hingga' => now()->addYears(5)->toDateString(),
        ], $overrides);

        return Anggota::query()->create($payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function renewalPayload(User $user): array
    {
        return [
            'nik' => $user->anggota->nik,
            'nama' => 'Guru Diperbarui',
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
            'nip' => '199001152020011001',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pas_foto' => UploadedFile::fake()->image('foto.jpg'),
            'sk_mengajar' => UploadedFile::fake()->create('sk.pdf', 120, 'application/pdf'),
        ];
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
