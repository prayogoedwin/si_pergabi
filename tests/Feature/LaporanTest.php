<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seedWilayah();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('laporan.index'))->assertRedirect(route('login'));
        $this->get(route('laporan.export'))->assertRedirect(route('login'));
    }

    public function test_anggota_cannot_view_laporan(): void
    {
        $this->actingAs(User::query()->where('email', 'anggota@example.com')->firstOrFail())
            ->get(route('laporan.index'))
            ->assertForbidden();
    }

    public function test_pengurus_sees_laporan_menu_and_tabular_sections(): void
    {
        $this->makeAnggota('36', '36.71', [
            'status' => Anggota::STATUS_AKTIF,
            'status_guru' => 'ASN',
            'jenjang' => 'SMA',
            'masa_berlaku_hingga' => now()->toDateString(),
        ]);
        $this->makeAnggota('32', '32.73', [
            'status' => Anggota::STATUS_TIDAK_AKTIF,
            'status_guru' => 'PPPK',
            'jenjang' => 'SD',
        ]);

        $this->actingAs($this->superAdmin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('laporan.index'))
            ->assertSee('Laporan');

        $this->actingAs($this->superAdmin())
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Jumlah anggota nasional')
            ->assertSee('Jumlah anggota per provinsi')
            ->assertSee('Jumlah anggota per kabupaten/kota')
            ->assertSee('Anggota aktif / tidak aktif')
            ->assertSee('Status guru')
            ->assertSee('Jenjang pendidikan')
            ->assertSee('Rekap pendaftaran '.now()->year)
            ->assertSee('Rekap perpanjangan '.now()->year)
            ->assertSee('Download Excel')
            ->assertSee('Provinsi (PD)')
            ->assertSee('Kabupaten/Kota (PC)')
            ->assertSee('Tahun rekap')
            ->assertSee('Banten')
            ->assertSee('Jawa Barat')
            ->assertSee('ASN')
            ->assertSee('PPPK')
            ->assertSee('Honorer')
            ->assertSee('SMA')
            ->assertDontSee('Total pendaftaran');
    }

    public function test_pp_can_filter_laporan_by_province_and_city(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('36', '36.72');
        $this->makeAnggota('32', '32.73');

        $this->actingAs($this->adminPp())
            ->get(route('laporan.index', ['provinsi' => '36']))
            ->assertOk()
            ->assertSee('PD Banten')
            ->assertSee('Kota Tangerang')
            ->assertSee('Kota Cilegon')
            ->assertDontSee('Kota Bandung');

        $this->actingAs($this->adminPp())
            ->get(route('laporan.index', ['provinsi' => '36', 'kabupaten' => '36.71']))
            ->assertOk()
            ->assertSee('PC Kota Tangerang')
            ->assertDontSee('Kota Bandung');
    }

    public function test_admin_pd_only_sees_own_province_and_kabupaten_filter(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('32', '32.73');

        $this->actingAs($this->adminPd())
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Jumlah anggota')
            ->assertSee('Kota Tangerang')
            ->assertDontSee('Semua provinsi')
            ->assertSee('Semua kabupaten/kota')
            ->assertDontSee('Kota Bandung')
            ->assertDontSee('Jawa Barat');
    }

    public function test_admin_pc_only_sees_own_city_without_wilayah_filters(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('36', '36.72');

        $this->actingAs($this->adminPc())
            ->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Kota Tangerang')
            ->assertDontSee('Semua provinsi')
            ->assertDontSee('Semua kabupaten/kota')
            ->assertDontSee('Kota Cilegon');
    }

    public function test_pengurus_can_download_excel(): void
    {
        $this->travelTo(now()->startOfDay());
        $this->makeAnggota('36', '36.71');

        $this->actingAs($this->adminPp())
            ->get(route('laporan.export', ['tahun' => now()->year]))
            ->assertOk()
            ->assertDownload('laporan-anggota-'.now()->year.'-'.now()->format('Y-m-d').'.xlsx');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeAnggota(string $pd, string $pc, array $overrides = []): Anggota
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ANGGOTA);

        return Anggota::query()->create(array_merge([
            'user_id' => $user->id,
            'nik' => fake()->unique()->numerify('3201############'),
            'nama' => $user->name,
            'jenis_kelamin' => 'L',
            'tempat_lahir' => 'Tangerang',
            'tanggal_lahir' => '1990-01-15',
            'agama' => 'Buddha',
            'status_perkawinan' => 'Kawin',
            'hp' => '081234567890',
            'whatsapp' => '081234567890',
            'email' => $user->email,
            'alamat' => 'Jl. Melati No. 1',
            'provinsi_kode' => $pd,
            'kabupaten_kode' => $pc,
            'kecamatan_kode' => $pc.'.01',
            'kelurahan_kode' => $pc.'.01.1001',
            'kode_pos' => '15111',
            'status_guru' => 'ASN',
            'jenjang' => 'SMA',
            'nama_sekolah' => 'SMA Negeri 1',
            'status_sekolah' => 'Negeri',
            'alamat_sekolah' => 'Jl. Sekolah No. 2',
            'pd_kode' => $pd,
            'pc_kode' => $pc,
            'status' => Anggota::STATUS_MENUNGGU_VERIFIKASI_PC,
        ], $overrides));
    }

    private function seedWilayah(): void
    {
        Wilayah::query()->insert([
            ['kode' => '32', 'nama' => 'Jawa Barat'],
            ['kode' => '32.73', 'nama' => 'Kota Bandung'],
            ['kode' => '36', 'nama' => 'Banten'],
            ['kode' => '36.71', 'nama' => 'Kota Tangerang'],
            ['kode' => '36.72', 'nama' => 'Kota Cilegon'],
        ]);
    }

    private function superAdmin(): User
    {
        return User::query()->where('email', 'superadmin@example.com')->firstOrFail();
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
