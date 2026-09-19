<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KtaQrVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_url_verifikasi_qr_uses_query_kode(): void
    {
        $anggota = $this->makeAktif();

        $this->assertSame(
            url('/verifikasi-qr-anggota').'/?kode='.rawurlencode((string) $anggota->nomor_anggota),
            $anggota->urlVerifikasiQr(),
        );
    }

    public function test_public_page_shows_valid_member_data(): void
    {
        $anggota = $this->makeAktif();

        $this->get('/verifikasi-qr-anggota/?kode='.$anggota->nomor_anggota)
            ->assertOk()
            ->assertSee('Kartu ini valid')
            ->assertSee($anggota->namaLengkap())
            ->assertSee($anggota->nomor_anggota)
            ->assertSee($anggota->nik)
            ->assertSee($anggota->ttlLabel())
            ->assertSee($anggota->instansiLabel())
            ->assertSee($anggota->alamatLabel())
            ->assertSee($anggota->labelPdPergabi())
            ->assertSee($anggota->masaBerlakuLabel())
            ->assertSee('Aktif')
            ->assertSee('NB:')
            ->assertSee('Kartu Tanda Anggota')
            ->assertDontSee('Tidak valid atau tidak ditemukan');
    }

    public function test_public_page_shows_not_found_for_unknown_kode(): void
    {
        $this->get('/verifikasi-qr-anggota/?kode=2099.99.9999.999')
            ->assertOk()
            ->assertSee('Tidak valid atau tidak ditemukan')
            ->assertSee('2099.99.9999.999')
            ->assertSee('NB:')
            ->assertDontSee('Kartu ini valid');
    }

    public function test_public_page_shows_not_found_without_kode(): void
    {
        $this->get('/verifikasi-qr-anggota')
            ->assertOk()
            ->assertSee('Tidak valid atau tidak ditemukan')
            ->assertDontSee('Kartu ini valid');
    }

    public function test_inactive_member_is_shown_as_invalid(): void
    {
        $anggota = $this->makeAktif();
        $anggota->update(['status' => Anggota::STATUS_TIDAK_AKTIF]);

        $this->get($anggota->urlVerifikasiQr())
            ->assertOk()
            ->assertSee('Kartu tidak valid')
            ->assertSee('Tidak aktif')
            ->assertSee($anggota->namaLengkap())
            ->assertSee('NB:');
    }

    public function test_legacy_path_redirects_to_query_kode(): void
    {
        $anggota = $this->makeAktif();

        $this->get('/verifikasi/'.$anggota->nomor_anggota)
            ->assertRedirect($anggota->urlVerifikasiQr());
    }

    public function test_underscore_path_redirects_to_hyphen_path(): void
    {
        $anggota = $this->makeAktif();

        $this->get('/verifikasi_qr-anggota/?kode='.$anggota->nomor_anggota)
            ->assertRedirect(route('kta.verifikasi', ['kode' => $anggota->nomor_anggota]));
    }

    private function makeAktif(): Anggota
    {
        $user = User::factory()->create([
            'name' => 'Guru QR',
            'email' => 'guru.qr@example.com',
        ]);
        $user->assignRole(Role::ANGGOTA);

        return Anggota::query()->create([
            'user_id' => $user->id,
            'nik' => '3201010101010088',
            'nama' => 'Guru QR',
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
            'status' => Anggota::STATUS_AKTIF,
            'nomor_anggota' => now()->year.'.36.3671.088',
            'tanggal_bergabung' => now()->toDateString(),
            'masa_berlaku_hingga' => now()->addYears(3)->toDateString(),
        ]);
    }
}
