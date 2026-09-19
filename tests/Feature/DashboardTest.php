<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Role;
use App\Models\User;
use App\Models\Wilayah;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('PERGABI')
            ->assertSee('logo-pergabi.png')
            ->assertDontSee('Example two level')
            ->assertDontSee('Example three level');
    }

    public function test_pengurus_dashboard_shows_registration_status_counts(): void
    {
        $this->makeAnggota('36', '36.71');

        $this->actingAs($this->superAdmin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Status pendaftaran')
            ->assertSee('Menunggu verifikasi PC')
            ->assertSee('Nasional')
            ->assertSee('Lihat per wilayah');
    }

    public function test_admin_pc_counts_only_own_city(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('36', '36.72');
        $this->makeAnggota('32', '32.73');

        $html = $this->actingAs($this->adminPc())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('PC')
            ->assertDontSee('Lihat per wilayah')
            ->getContent();

        $this->assertDashboardTotal($html, 1);
    }

    public function test_admin_pd_counts_only_own_province(): void
    {
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('36', '36.72');
        $this->makeAnggota('32', '32.73');

        $html = $this->actingAs($this->adminPd())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('PD')
            ->assertDontSee('Lihat per wilayah')
            ->getContent();

        $this->assertDashboardTotal($html, 2);
    }

    public function test_pp_and_super_admin_can_filter_by_province_and_city(): void
    {
        $this->seedWilayah();
        $this->makeAnggota('36', '36.71');
        $this->makeAnggota('36', '36.72');
        $this->makeAnggota('32', '32.73');

        $national = $this->actingAs($this->adminPp())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lihat per wilayah')
            ->assertSee('Banten')
            ->assertSee('select2.min.js', false)
            ->getContent();
        $this->assertDashboardTotal($national, 3);

        $provinsi = $this->actingAs($this->superAdmin())
            ->get(route('dashboard', ['provinsi' => '36']))
            ->assertOk()
            ->getContent();
        $this->assertDashboardTotal($provinsi, 2);

        $kabupaten = $this->actingAs($this->superAdmin())
            ->get(route('dashboard', ['provinsi' => '36', 'kabupaten' => '36.71']))
            ->assertOk()
            ->getContent();
        $this->assertDashboardTotal($kabupaten, 1);
    }

    public function test_admin_pc_cannot_view_anggota_from_another_city(): void
    {
        $own = $this->makeAnggota('36', '36.71');
        $other = $this->makeAnggota('32', '32.73');

        $this->actingAs($this->adminPc())
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertSee($own->nama)
            ->assertDontSee($other->nama);

        $this->actingAs($this->adminPc())
            ->get(route('anggota.show', $other))
            ->assertForbidden();
    }

    public function test_admin_pp_can_filter_anggota_by_provinsi_and_kabupaten(): void
    {
        $this->seedWilayah();
        $banten = $this->makeAnggota('36', '36.71');
        $cilegon = $this->makeAnggota('36', '36.72');
        $bandung = $this->makeAnggota('32', '32.73');

        $this->actingAs($this->adminPp())
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertSee('Provinsi (PD)')
            ->assertSee('Kabupaten/Kota (PC)')
            ->assertSee($banten->nama)
            ->assertSee($cilegon->nama)
            ->assertSee($bandung->nama);

        $this->actingAs($this->adminPp())
            ->get(route('anggota.index', ['provinsi' => '36']))
            ->assertOk()
            ->assertSee($banten->nama)
            ->assertSee($cilegon->nama)
            ->assertDontSee($bandung->nama);

        $this->actingAs($this->adminPp())
            ->get(route('anggota.index', ['provinsi' => '36', 'kabupaten' => '36.71']))
            ->assertOk()
            ->assertSee($banten->nama)
            ->assertDontSee($cilegon->nama)
            ->assertDontSee($bandung->nama);
    }

    public function test_admin_pd_only_sees_own_province_and_can_filter_kabupaten(): void
    {
        $this->seedWilayah();
        $tangerang = $this->makeAnggota('36', '36.71');
        $cilegon = $this->makeAnggota('36', '36.72');
        $bandung = $this->makeAnggota('32', '32.73');

        $this->actingAs($this->adminPd())
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertDontSee('Provinsi (PD)')
            ->assertSee('Kabupaten/Kota (PC)')
            ->assertSee($tangerang->nama)
            ->assertSee($cilegon->nama)
            ->assertDontSee($bandung->nama);

        $this->actingAs($this->adminPd())
            ->get(route('anggota.index', ['provinsi' => '32', 'kabupaten' => '32.73']))
            ->assertOk()
            ->assertDontSee($bandung->nama)
            ->assertSee($tangerang->nama);

        $this->actingAs($this->adminPd())
            ->get(route('anggota.index', ['kabupaten' => '36.71']))
            ->assertOk()
            ->assertSee($tangerang->nama)
            ->assertDontSee($cilegon->nama);
    }

    public function test_admin_pc_does_not_see_wilayah_filters(): void
    {
        $this->seedWilayah();
        $own = $this->makeAnggota('36', '36.71');
        $other = $this->makeAnggota('36', '36.72');

        $this->actingAs($this->adminPc())
            ->get(route('anggota.index'))
            ->assertOk()
            ->assertDontSee('Provinsi (PD)')
            ->assertDontSee('Kabupaten/Kota (PC)')
            ->assertSee($own->nama)
            ->assertDontSee($other->nama);
    }

    public function test_admin_can_reset_anggota_password_in_scope(): void
    {
        $this->seedWilayah();
        $anggota = $this->makeAnggota('36', '36.71');
        $other = $this->makeAnggota('32', '32.73');

        $response = $this->actingAs($this->adminPc())
            ->from(route('anggota.show', $anggota))
            ->post(route('anggota.reset-password', $anggota));

        $response->assertRedirect()->assertSessionHas('password_reset');

        $plain = $this->app['session']->get('password_reset');
        $this->assertTrue(Hash::check($plain, $anggota->user->fresh()->password));

        $this->actingAs($this->adminPc())
            ->post(route('anggota.reset-password', $other))
            ->assertForbidden();
    }

    public function test_admin_can_print_kta_from_anggota_detail_when_aktif(): void
    {
        $this->seedWilayah();
        $pending = $this->makeAnggota('36', '36.71');
        $aktif = $this->makeAnggota('36', '36.71');
        $aktif->update([
            'status' => Anggota::STATUS_AKTIF,
            'nomor_anggota' => now()->year.'.36.3671.001',
            'tanggal_bergabung' => now()->toDateString(),
            'masa_berlaku_hingga' => now()->addYears(3)->toDateString(),
        ]);

        $this->actingAs($this->adminPp())
            ->get(route('anggota.show', $pending))
            ->assertOk()
            ->assertSee('Reset password')
            ->assertSee('Kartu tanda anggota')
            ->assertDontSee('Cetak ID Card');

        $this->actingAs($this->adminPp())
            ->get(route('anggota.show', $aktif))
            ->assertOk()
            ->assertSee('Cetak ID Card')
            ->assertSee('Cetak A4 / PDF')
            ->assertSee('Unduh PNG')
            ->assertSee('kta-demo-front', false)
            ->assertSee($aktif->nomor_anggota)
            ->assertSee('verifikasi-qr-anggota', false)
            ->assertSee('kode=', false)
            ->assertSee('data-kta-qr', false)
            ->assertSee('js/pergabi-qr.js', false);

        $this->actingAs($this->adminPp())
            ->get(route('anggota.qr', $aktif))
            ->assertOk()
            ->assertSee('QR Code')
            ->assertSee('data-anggota-qr', false)
            ->assertSee($aktif->nomor_anggota);

        $this->actingAs($this->adminPp())
            ->get(route('anggota.qr', $pending))
            ->assertOk()
            ->assertSee('QR Code belum tersedia');
    }

    private function assertDashboardTotal(string $html, int $expected): void
    {
        $this->assertMatchesRegularExpression(
            '/Total pendaftaran<\/p>\s*<p class="text-2xl[^"]*">'.$expected.'<\/p>/',
            $html,
        );
    }

    private function makeAnggota(string $pd, string $pc): Anggota
    {
        $user = User::factory()->create();
        $user->assignRole(Role::ANGGOTA);

        return Anggota::query()->create([
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
        ]);
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
